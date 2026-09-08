<?php

namespace App\Services;

use App\Models\AchievementReminder;
use App\Models\ExerciseRecord;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightGoal;
use Carbon\Carbon;

class AchievementService
{
    private const BADGES = [
        'first_register' => ['name' => '初来乍到', 'description' => '完成注册', 'icon' => '🎉'],
        'streak_3' => ['name' => '三日坚持', 'description' => '连续3天记录', 'icon' => '🔥'],
        'streak_7' => ['name' => '一周达人', 'description' => '连续7天记录', 'icon' => '⭐'],
        'streak_30' => ['name' => '月度冠军', 'description' => '连续30天记录', 'icon' => '🏆'],
        'first_goal' => ['name' => '目标设定', 'description' => '首次设定减重目标', 'icon' => '🎯'],
        'goal_5' => ['name' => '小有成效', 'description' => '达成减重目标5%', 'icon' => '📈'],
        'goal_50' => ['name' => '半程英雄', 'description' => '达成减重目标50%', 'icon' => '💪'],
        'goal_100' => ['name' => '目标达人', 'description' => '达成减重目标100%', 'icon' => '🏅'],
        'first_exercise' => ['name' => '运动新手', 'description' => '首次记录运动', 'icon' => '🏃'],
        'exercise_100' => ['name' => '运动达人', 'description' => '累计运动100分钟', 'icon' => '💪'],
    ];

    public function checkAchievements(int $userId): array
    {
        $user = User::findOrFail($userId);
        $newlyEarned = [];

        // First register - always available
        if ($this->canEarn($userId, 'first_register')) {
            $this->earnBadge($userId, 'first_register');
            $newlyEarned[] = 'first_register';
        }

        // Streak badges
        $streak = $this->calculateStreak($userId);
        if ($streak >= 3 && $this->canEarn($userId, 'streak_3')) {
            $this->earnBadge($userId, 'streak_3');
            $newlyEarned[] = 'streak_3';
        }
        if ($streak >= 7 && $this->canEarn($userId, 'streak_7')) {
            $this->earnBadge($userId, 'streak_7');
            $newlyEarned[] = 'streak_7';
        }
        if ($streak >= 30 && $this->canEarn($userId, 'streak_30')) {
            $this->earnBadge($userId, 'streak_30');
            $newlyEarned[] = 'streak_30';
        }

        // First goal
        if (WeightGoal::where('user_id', $userId)->exists() && $this->canEarn($userId, 'first_goal')) {
            $this->earnBadge($userId, 'first_goal');
            $newlyEarned[] = 'first_goal';
        }

        // Goal progress badges
        $goalProgress = $this->calculateGoalProgress($userId);
        if ($goalProgress >= 5 && $this->canEarn($userId, 'goal_5')) {
            $this->earnBadge($userId, 'goal_5');
            $newlyEarned[] = 'goal_5';
        }
        if ($goalProgress >= 50 && $this->canEarn($userId, 'goal_50')) {
            $this->earnBadge($userId, 'goal_50');
            $newlyEarned[] = 'goal_50';
        }
        if ($goalProgress >= 100 && $this->canEarn($userId, 'goal_100')) {
            $this->earnBadge($userId, 'goal_100');
            $newlyEarned[] = 'goal_100';
        }

        // First exercise
        if (ExerciseRecord::where('user_id', $userId)->exists() && $this->canEarn($userId, 'first_exercise')) {
            $this->earnBadge($userId, 'first_exercise');
            $newlyEarned[] = 'first_exercise';
        }

        // Exercise 100 minutes
        $totalMinutes = ExerciseRecord::where('user_id', $userId)->sum('duration_minutes');
        if ($totalMinutes >= 100 && $this->canEarn($userId, 'exercise_100')) {
            $this->earnBadge($userId, 'exercise_100');
            $newlyEarned[] = 'exercise_100';
        }

        return $newlyEarned;
    }

    public function earnBadge(int $userId, string $badgeType): void
    {
        AchievementReminder::create([
            'user_id' => $userId,
            'type' => 'achievement',
            'badge_type' => $badgeType,
            'earned_at' => now(),
        ]);
    }

    public function getBadges(int $userId): array
    {
        $earned = AchievementReminder::where('user_id', $userId)
            ->where('type', 'achievement')
            ->whereNotNull('badge_type')
            ->pluck('badge_type')
            ->toArray();

        $badges = [];
        foreach (self::BADGES as $key => $badge) {
            $badges[$key] = array_merge($badge, [
                'earned' => in_array($key, $earned),
                'earned_at' => AchievementReminder::where('user_id', $userId)
                    ->where('badge_type', $key)
                    ->value('earned_at'),
            ]);
        }

        return $badges;
    }

    public function getAchievementWall(int $userId): array
    {
        return $this->getBadges($userId);
    }

    private function canEarn(int $userId, string $badgeType): bool
    {
        return ! AchievementReminder::where('user_id', $userId)
            ->where('badge_type', $badgeType)
            ->exists();
    }

    private function calculateStreak(int $userId): int
    {
        $streak = 0;
        $date = Carbon::now();

        while (true) {
            $hasRecord = MealRecord::where('user_id', $userId)
                ->whereDate('date', $date->toDateString())
                ->exists();

            if (! $hasRecord) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    private function calculateGoalProgress(int $userId): float
    {
        $goal = WeightGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $goal) {
            return 0;
        }

        $latestWeight = \App\Models\WeightRecord::where('user_id', $userId)
            ->latest('date')
            ->value('weight_kg');

        if (! $latestWeight) {
            return 0;
        }

        $totalToLose = (float) $goal->start_weight - (float) $goal->target_weight;
        if ($totalToLose <= 0) {
            return 0;
        }

        $lost = (float) $goal->start_weight - (float) $latestWeight;

        return max(0, min(100, ($lost / $totalToLose) * 100));
    }
}
