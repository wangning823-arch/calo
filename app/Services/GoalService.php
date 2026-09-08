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
    private const DEFAULT_DEFICIT = 500;
    private const MIN_DEFICIT = 300;
    private const MAX_DEFICIT = 750;

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

    public function calculateDailyBudget(User $user, float $deficit = null): float
    {
        $userService = app(UserService::class);
        $tdee = $userService->calculateTDEE($user);

        $deficit = $deficit ?? self::DEFAULT_DEFICIT;
        $deficit = max(self::MIN_DEFICIT, min(self::MAX_DEFICIT, $deficit));

        $minCalories = $user->gender === 'female' ? self::MIN_CALORIES_FEMALE : self::MIN_CALORIES_MALE;

        $budget = $tdee - $deficit;

        return max($budget, $minCalories);
    }

    public function createGoal(User $user, array $data): WeightGoal
    {
        $latestWeight = $user->weightRecords()->latest('date')->first();
        $startWeight = (float) $latestWeight->weight_kg;

        $deficit = $data['target_deficit'] ?? self::DEFAULT_DEFICIT;
        $dailyBudget = $this->calculateDailyBudget($user, $deficit);

        return WeightGoal::create([
            'user_id' => $user->id,
            'mode' => 'lose',
            'start_weight' => $startWeight,
            'target_weight' => $data['target_weight'],
            'target_date' => $data['target_date'],
            'daily_calorie_budget' => $dailyBudget,
            'target_deficit' => $deficit,
            'status' => 'active',
        ]);
    }

    public function updateGoal(WeightGoal $goal, array $data): WeightGoal
    {
        $user = $goal->user;
        $deficit = $data['target_deficit'] ?? $goal->target_deficit;
        $dailyBudget = $this->calculateDailyBudget($user, $deficit);

        $goal->update([
            'target_weight' => $data['target_weight'] ?? $goal->target_weight,
            'target_date' => $data['target_date'] ?? $goal->target_date,
            'daily_calorie_budget' => $dailyBudget,
            'target_deficit' => $deficit,
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
        $weeksNeeded = ceil($weightToLose / self::MAX_WEEKLY_LOSS_NORMAL);

        return Carbon::now()->addWeeks($weeksNeeded)->format('Y年m月d日');
    }
}
