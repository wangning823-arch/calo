<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;

class DashboardService
{
    public function getTodayData(int $userId): array
    {
        $user = User::findOrFail($userId);
        $today = Carbon::now()->toDateString();

        // Maintenance calories (TDEE): intake at this level keeps weight stable
        $maintenance = null;
        if ($user->height && $user->gender) {
            $maintenance = round(app(UserService::class)->calculateTDEE($user));
        }

        // Goal intake ceiling and expected daily deficit
        $goal = $user->activeWeightGoal;
        $budget = $goal ? (float) $goal->daily_calorie_budget : null;
        $targetDeficit = $goal ? (float) $goal->target_deficit : null;

        // Get today's meal records
        $meals = MealRecord::where('user_id', $userId)
            ->whereDate('date', $today)
            ->with('food')
            ->get();

        $intakeCalories = 0;
        $intakeProtein = 0;
        $intakeCarbs = 0;
        $intakeFat = 0;
        $byMeal = [];

        foreach ($meals as $meal) {
            $food = $meal->food;
            if (! $food) {
                continue;
            }
            $factor = $meal->serving_grams / 100;
            $calories = $food->calories_per_100g * $factor;
            $protein = $food->protein_per_100g * $factor;
            $carbs = $food->carbs_per_100g * $factor;
            $fat = $food->fat_per_100g * $factor;

            $intakeCalories += $calories;
            $intakeProtein += $protein;
            $intakeCarbs += $carbs;
            $intakeFat += $fat;

            $mealType = $meal->meal_type;
            if (! isset($byMeal[$mealType])) {
                $byMeal[$mealType] = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0, 'count' => 0];
            }
            $byMeal[$mealType]['calories'] += $calories;
            $byMeal[$mealType]['protein'] += $protein;
            $byMeal[$mealType]['carbs'] += $carbs;
            $byMeal[$mealType]['fat'] += $fat;
            $byMeal[$mealType]['count']++;
        }

        // Get today's exercise records
        $exercises = ExerciseRecord::where('user_id', $userId)
            ->whereDate('date', $today)
            ->get();

        $burnedCalories = 0;
        foreach ($exercises as $exercise) {
            $burnedCalories += (float) $exercise->estimated_calories;
        }

        // Today's intake ceiling = goal intake + exercise (exercise expands what you can eat)
        $dynamicBudget = $budget !== null ? round($budget + $burnedCalories, 1) : null;
        $remaining = $dynamicBudget !== null ? round($dynamicBudget - $intakeCalories, 1) : null;

        // Actual deficit vs maintenance: (TDEE + exercise) - intake
        // Positive = below maintenance (weight loss); negative = surplus
        $actualDeficit = $maintenance !== null
            ? round($maintenance + $burnedCalories - $intakeCalories, 1)
            : null;

        // How far actual deficit is from the goal target (positive = on track / ahead)
        $deficitGap = ($targetDeficit !== null && $actualDeficit !== null)
            ? round($actualDeficit - $targetDeficit, 1)
            : null;

        // Determine status (based on dynamic intake ceiling)
        $status = 'normal';
        if ($dynamicBudget !== null && $dynamicBudget > 0) {
            $ratio = $intakeCalories / $dynamicBudget;
            if ($ratio > 1.25) {
                $status = 'over';
            } elseif ($ratio > 1.1) {
                $status = 'warning';
            } elseif ($ratio >= 0.9) {
                $status = 'good';
            }
        }

        return [
            'budget' => $budget,
            'dynamic_budget' => $dynamicBudget,
            'maintenance' => $maintenance,
            'target_deficit' => $targetDeficit !== null ? (float) $targetDeficit : null,
            'actual_deficit' => $actualDeficit,
            'deficit_gap' => $deficitGap,
            'intake_calories' => round($intakeCalories, 1),
            'intake_protein' => round($intakeProtein, 1),
            'intake_carbs' => round($intakeCarbs, 1),
            'intake_fat' => round($intakeFat, 1),
            'burned_calories' => round($burnedCalories, 1),
            'remaining' => $remaining,
            'status' => $status,
            'meal_count' => $meals->count(),
            'by_meal' => array_map(fn($m) => [
                'calories' => round($m['calories']),
                'protein' => round($m['protein'], 1),
                'carbs' => round($m['carbs'], 1),
                'fat' => round($m['fat'], 1),
                'count' => $m['count'],
            ], $byMeal),
        ];
    }

    public function getStreakDays(int $userId): int
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

    public function getRecentWeight(int $userId): ?array
    {
        $record = WeightRecord::where('user_id', $userId)
            ->latest('date')
            ->first();

        if (! $record) {
            return null;
        }

        return [
            'weight_kg' => (float) $record->weight_kg,
            'date' => $record->date->toDateString(),
        ];
    }
}
