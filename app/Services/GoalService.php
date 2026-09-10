<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeightGoal;
use Carbon\Carbon;

class GoalService
{
    private const MAX_WEEKLY_LOSS_NORMAL = 1.0; // kg/week
    private const MAX_WEEKLY_LOSS_WARNING = 1.5; // kg/week
    private const MIN_CALORIES_FEMALE = 1200;
    private const MIN_CALORIES_MALE = 1500;
    private const KCAL_PER_KG_FAT = 7700;

    public function validateGoal(User $user, float $targetWeight, string $targetDate): array
    {
        $latestWeight = $user->weightRecords()->latest('date')->first();
        $currentWeight = $latestWeight ? (float) $latestWeight->weight_kg : null;

        if (! $currentWeight) {
            return ['valid' => false, 'message' => '请先记录当前体重。'];
        }

        // Check if user has height for BMI calculation
        if (! $user->height) {
            return ['valid' => false, 'message' => '请先填写身高信息。'];
        }

        $heightM = (float) $user->height / 100;
        $targetBmi = $targetWeight / ($heightM * $heightM);

        if ($targetBmi < 18.5) {
            return ['valid' => false, 'message' => '目标体重对应的BMI为'.round($targetBmi, 1).'，低于健康标准(18.5)。'];
        }

        // Check current BMI
        $currentBmi = $currentWeight / ($heightM * $heightM);
        if ($currentBmi < 18.5) {
            return ['valid' => false, 'message' => '您当前BMI偏低，不建议设定减重目标。'];
        }

        // Special group check
        if (in_array($user->special_group, ['pregnant', 'lactating'])) {
            return ['valid' => false, 'message' => '孕期/哺乳期不建议设定减重目标。'];
        }

        $date = Carbon::parse($targetDate);
        $now = Carbon::now();

        if ($date->lte($now)) {
            return ['valid' => false, 'message' => '目标日期不能是过去。'];
        }

        $weeksRemaining = max(1, $now->diffInWeeks($date));

        $weightToLose = $currentWeight - $targetWeight;

        if ($weightToLose <= 0) {
            return ['valid' => false, 'message' => '目标体重应低于当前体重。'];
        }

        $weeklyRate = $weightToLose / $weeksRemaining;

        $status = 'normal';
        $warning = null;

        if ($weeklyRate > self::MAX_WEEKLY_LOSS_WARNING) {
            return [
                'valid' => false,
                'message' => "周均减重速率为".number_format($weeklyRate, 1)."kg/周，超过安全上限(1.5kg/周)。建议将目标日期设为".$this->suggestTargetDate($currentWeight, $targetWeight)."或调整目标体重。",
            ];
        } elseif ($weeklyRate > self::MAX_WEEKLY_LOSS_NORMAL) {
            $status = 'warning';
            $warning = "周均减重速率为".number_format($weeklyRate, 1)."kg/周，略高于推荐值(1kg/周)。请确认是否继续。";
        }

        return [
            'valid' => true,
            'status' => $status,
            'warning' => $warning,
            'weekly_rate' => round($weeklyRate, 2),
            'weeks_remaining' => $weeksRemaining,
            'current_weight' => $currentWeight,
            'weight_to_lose' => round($weightToLose, 1),
        ];
    }

    public function calculateRequiredDeficit(float $currentWeight, float $targetWeight, string $targetDate): array
    {
        $weightToLose = $currentWeight - $targetWeight;
        $daysRemaining = max(1, Carbon::now()->diffInDays(Carbon::parse($targetDate)));
        $weeksRemaining = max(1, $daysRemaining / 7);

        $dailyDeficit = ($weightToLose * self::KCAL_PER_KG_FAT) / $daysRemaining;

        $weeklyRate = $weightToLose / $weeksRemaining;

        return [
            'daily_deficit' => round($dailyDeficit),
            'weight_to_lose' => round($weightToLose, 1),
            'days_remaining' => $daysRemaining,
            'weekly_rate' => round($weeklyRate, 2),
            'is_extreme' => $weeklyRate > self::MAX_WEEKLY_LOSS_WARNING,
            'is_warning' => $weeklyRate > self::MAX_WEEKLY_LOSS_NORMAL,
        ];
    }

    public function calculateDailyBudget(User $user, float $deficit): array
    {
        $userService = app(UserService::class);
        $tdee = $userService->calculateTDEE($user);

        $minCalories = $user->gender === 'female' ? self::MIN_CALORIES_FEMALE : self::MIN_CALORIES_MALE;
        $maxSafeDeficit = max(0, round($tdee - $minCalories));
        $isOverSafe = $deficit > $maxSafeDeficit;

        // Respect the user's plan. Over-safe only triggers a warning + confirm.
        $plannedDeficit = round($deficit);
        $intake = round($tdee - $deficit);

        return [
            'budget' => $intake,
            'tdee' => round($tdee),
            'min_calories' => $minCalories,
            'raw_budget' => $intake,
            'is_floored' => $isOverSafe,
            'is_over_safe' => $isOverSafe,
            'planned_deficit' => $plannedDeficit,
            'max_safe_deficit' => $maxSafeDeficit,
            // BC alias used by older callers
            'actual_deficit' => $plannedDeficit,
        ];
    }

    public function createGoal(User $user, array $data): WeightGoal
    {
        $latestWeight = $user->weightRecords()->latest('date')->first();
        $startWeight = (float) $latestWeight->weight_kg;

        $calc = $this->calculateRequiredDeficit($startWeight, $data['target_weight'], $data['target_date']);
        $budgetResult = $this->calculateDailyBudget($user, $calc['daily_deficit']);

        return WeightGoal::create([
            'user_id' => $user->id,
            'mode' => 'lose',
            'start_weight' => $startWeight,
            'target_weight' => $data['target_weight'],
            'target_date' => $data['target_date'],
            'daily_calorie_budget' => $budgetResult['budget'],
            'target_deficit' => $budgetResult['planned_deficit'],
            'status' => 'active',
        ]);
    }

    public function updateGoal(WeightGoal $goal, array $data): WeightGoal
    {
        $user = $goal->user;
        $targetWeight = $data['target_weight'] ?? $goal->target_weight;
        $targetDate = $data['target_date'] ?? $goal->target_date;
        $startWeight = (float) $goal->start_weight;

        $calc = $this->calculateRequiredDeficit($startWeight, $targetWeight, $targetDate);
        $budgetResult = $this->calculateDailyBudget($user, $calc['daily_deficit']);

        $goal->update([
            'target_weight' => $targetWeight,
            'target_date' => $targetDate,
            'daily_calorie_budget' => $budgetResult['budget'],
            'target_deficit' => $budgetResult['planned_deficit'],
        ]);

        return $goal->fresh();
    }

    public function getCurrentGoal(User $user): ?WeightGoal
    {
        return $user->activeWeightGoal;
    }

    private function suggestTargetDate(float $currentWeight, float $targetWeight): string
    {
        $weightToLose = $currentWeight - $targetWeight;
        // Prefer the sustainable rate; still reject plans faster than the hard limit
        $weeksNeeded = ceil($weightToLose / 0.5);

        return Carbon::now()->addWeeks($weeksNeeded)->format('Y年m月d日');
    }
}
