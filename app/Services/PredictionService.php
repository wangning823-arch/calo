<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;

class PredictionService
{
    public function predictGoalAchievement(User $user): ?array
    {
        $goal = $user->activeWeightGoal;
        if (! $goal) {
            return null;
        }

        $records = WeightRecord::where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subDays(14)->toDateString())
            ->orderBy('date')
            ->get();

        if ($records->count() < 7) {
            return [
                'predictable' => false,
                'message' => '还需记录' . (7 - $records->count()) . '天即可显示目标预测。',
                'days_needed' => 7 - $records->count(),
            ];
        }

        // Calculate average daily weight loss over last 14 days
        $firstWeight = (float) $records->first()->weight_kg;
        $lastWeight = (float) $records->last()->weight_kg;
        $days = $records->first()->date->diffInDays($records->last()->date);

        if ($days < 1) {
            return ['predictable' => false, 'message' => '数据不足，无法预测。'];
        }

        $dailyRate = ($firstWeight - $lastWeight) / $days;

        $currentWeight = $lastWeight;
        $targetWeight = (float) $goal->target_weight;

        $weightRemaining = $currentWeight - $targetWeight;

        if ($weightRemaining <= 0) {
            return [
                'predictable' => true,
                'achieved' => true,
                'message' => '恭喜！您已达到目标体重！',
            ];
        }

        if ($dailyRate <= 0) {
            return [
                'predictable' => true,
                'achieved' => false,
                'daily_rate' => 0,
                'message' => '近期体重未下降，按当前趋势无法预测达成日期。',
                'suggestion' => '建议调整饮食或运动计划。',
            ];
        }

        $daysToGoal = ceil($weightRemaining / $dailyRate);
        $predictedDate = Carbon::now()->addDays($daysToGoal);

        $targetDate = Carbon::parse($goal->target_date);
        $onTrack = $predictedDate->lte($targetDate);

        return [
            'predictable' => true,
            'achieved' => false,
            'predicted_date' => $predictedDate->format('Y-m-d'),
            'predicted_date_display' => $predictedDate->format('Y年m月d日'),
            'days_to_goal' => $daysToGoal,
            'daily_rate' => round($dailyRate * 1000, 1), // g/day
            'weight_remaining' => round($weightRemaining, 1),
            'target_date' => $targetDate->format('Y-m-d'),
            'on_track' => $onTrack,
            'message' => $onTrack
                ? '按当前进度，预计' . $predictedDate->format('m月d日') . '达成目标。'
                : '按当前进度，预计' . $predictedDate->format('m月d日') . '达成目标，晚于目标日期。建议增加运动或调整饮食。',
        ];
    }

    public function detectPlateau(User $user): ?array
    {
        $records = WeightRecord::where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subDays(21)->toDateString())
            ->orderBy('date')
            ->get();

        if ($records->count() < 14) {
            return null;
        }

        // Check last 14 days for plateau (7-day moving average fluctuation < 0.25kg)
        $recent14 = $records->take(-14);
        $weights = $recent14->pluck('weight_kg')->map(fn($w) => (float) $w)->toArray();

        // Calculate 7-day moving averages
        $movingAvgs = [];
        for ($i = 6; $i < count($weights); $i++) {
            $window = array_slice($weights, $i - 6, 7);
            $movingAvgs[] = round(array_sum($window) / count($window), 2);
        }

        if (empty($movingAvgs)) {
            return null;
        }

        $maxAvg = max($movingAvgs);
        $minAvg = min($movingAvgs);
        $fluctuation = $maxAvg - $minAvg;

        // Plateau: 7-day moving average fluctuation < 0.25kg over 14 days
        $isPlateau = $fluctuation < 0.25;

        if (! $isPlateau) {
            return null;
        }

        return [
            'is_plateau' => true,
            'fluctuation_kg' => round($fluctuation, 2),
            'days' => count($weights),
            'message' => '过去14天体重波动仅' . round($fluctuation, 2) . 'kg，可能进入平台期。',
            'suggestions' => [
                '调整热量预算：减少100-200kcal',
                '更换运动方式：增加强度或时长',
                '增加蛋白质摄入',
                '保证充足睡眠（7-9小时）',
            ],
        ];
    }

    public function getGoalProgress(User $user): ?array
    {
        $goal = $user->activeWeightGoal;
        if (! $goal) {
            return null;
        }

        $latestWeight = $user->weightRecords()->latest('date')->first();
        if (! $latestWeight) {
            return null;
        }

        $currentWeight = (float) $latestWeight->weight_kg;
        $startWeight = (float) $goal->start_weight;
        $targetWeight = (float) $goal->target_weight;

        $totalToLose = $startWeight - $targetWeight;
        if ($totalToLose <= 0) {
            return ['progress' => 100, 'lost' => 0, 'remaining' => 0];
        }

        $lost = $startWeight - $currentWeight;
        $remaining = $currentWeight - $targetWeight;
        $progress = max(0, min(100, ($lost / $totalToLose) * 100));

        return [
            'progress' => round($progress, 1),
            'lost_kg' => round($lost, 1),
            'remaining_kg' => round(max(0, $remaining), 1),
            'start_weight' => $startWeight,
            'current_weight' => $currentWeight,
            'target_weight' => $targetWeight,
            'total_to_lose' => round($totalToLose, 1),
        ];
    }
}
