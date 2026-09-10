<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class HealthAlertService
{
    private const MIN_CALORIES_FEMALE = 1200;
    private const MIN_CALORIES_MALE = 1500;
    private const MAX_WEEKLY_LOSS_KG = 1.5;

    public function checkDailyAlert(User $user, string $date): ?array
    {
        $mealService = app(MealService::class);
        $summary = $mealService->getDailySummary($user->id, $date);

        $totalIntake = $summary['total_calories'] ?? 0;
        $minCalories = $user->gender === 'female' ? self::MIN_CALORIES_FEMALE : self::MIN_CALORIES_MALE;

        if ($totalIntake > 0 && $totalIntake < $minCalories) {
            return [
                'type' => 'daily_low_intake',
                'level' => 'warning',
                'message' => "今日摄入仅{$totalIntake}kcal，低于安全线{$minCalories}kcal。",
                'intake' => $totalIntake,
                'min_calories' => $minCalories,
            ];
        }

        return null;
    }

    public function checkWeeklyAlert(User $user): ?array
    {
        $records = $user->weightRecords()
            ->where('date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->orderBy('date')
            ->get();

        if ($records->count() < 2) {
            return null;
        }

        $firstWeight = (float) $records->first()->weight_kg;
        $lastWeight = (float) $records->last()->weight_kg;
        $days = $records->first()->date->diffInDays($records->last()->date);

        if ($days < 1) {
            return null;
        }

        $dailyRate = ($firstWeight - $lastWeight) / $days;
        $weeklyRate = $dailyRate * 7;

        if ($weeklyRate > self::MAX_WEEKLY_LOSS_KG) {
            return [
                'type' => 'weekly_fast_loss',
                'level' => 'danger',
                'message' => '本周减重' . round($weeklyRate, 2) . 'kg，超过安全上限（每周1.5kg）。',
                'weekly_rate_kg' => round($weeklyRate, 2),
            ];
        }

        return null;
    }

    public function getAlertLevel(User $user): array
    {
        $today = now()->toDateString();
        $dailyAlert = $this->checkDailyAlert($user, $today);

        if (! $dailyAlert) {
            return ['has_alert' => false, 'level' => null, 'consecutive_days' => 0];
        }

        // Count consecutive days of low intake
        $consecutiveDays = 0;
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $alert = $this->checkDailyAlert($user, $date);

            if ($alert) {
                $consecutiveDays++;
            } else {
                break;
            }
        }

        $level = match (true) {
            $consecutiveDays >= 7 => 'critical',
            $consecutiveDays >= 3 => 'persistent',
            default => 'first',
        };

        return [
            'has_alert' => true,
            'level' => $level,
            'consecutive_days' => $consecutiveDays,
            'alert' => $dailyAlert,
        ];
    }

    public function confirmAlert(User $user, string $choice): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'health_alert_confirmed',
            'action_details' => [
                'choice' => $choice,
                'date' => now()->toDateString(),
            ],
            'ip_address' => request()->ip(),
        ]);
    }
}
