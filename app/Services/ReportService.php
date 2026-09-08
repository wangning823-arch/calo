<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function generateWeeklyReport(int $userId, string $weekStart): array
    {
        $user = User::findOrFail($userId);
        $start = Carbon::parse($weekStart)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $days[] = $current->copy();
            $current->addDay();
        }

        $goal = $user->activeWeightGoal;
        $budget = $goal ? (float) $goal->daily_calorie_budget : 0;

        // Daily stats
        $dailyStats = [];
        $totalIntake = 0;
        $totalBurned = 0;
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;
        $daysWithRecords = 0;

        foreach ($days as $day) {
            $dayStr = $day->toDateString();
            $meals = MealRecord::where('user_id', $userId)
                ->whereDate('date', $dayStr)
                ->with('food')
                ->get();

            $dayIntake = 0;
            $dayProtein = 0;
            $dayCarbs = 0;
            $dayFat = 0;

            foreach ($meals as $meal) {
                $food = $meal->food;
                if (! $food) {
                    continue;
                }
                $factor = $meal->serving_grams / 100;
                $dayIntake += $food->calories_per_100g * $factor;
                $dayProtein += $food->protein_per_100g * $factor;
                $dayCarbs += $food->carbs_per_100g * $factor;
                $dayFat += $food->fat_per_100g * $factor;
            }

            $dayBurned = ExerciseRecord::where('user_id', $userId)
                ->whereDate('date', $dayStr)
                ->sum('estimated_calories');

            $totalIntake += $dayIntake;
            $totalBurned += $dayBurned;
            $totalProtein += $dayProtein;
            $totalCarbs += $dayCarbs;
            $totalFat += $dayFat;

            if ($meals->isNotEmpty()) {
                $daysWithRecords++;
            }

            $dailyStats[] = [
                'date' => $dayStr,
                'intake' => round($dayIntake, 1),
                'burned' => round($dayBurned, 1),
                'protein' => round($dayProtein, 1),
                'carbs' => round($dayCarbs, 1),
                'fat' => round($dayFat, 1),
            ];
        }

        // Weight change
        $startWeight = WeightRecord::where('user_id', $userId)
            ->whereDate('date', '<=', $start->toDateString())
            ->latest('date')
            ->value('weight_kg');

        $endWeight = WeightRecord::where('user_id', $userId)
            ->whereDate('date', '<=', $end->toDateString())
            ->latest('date')
            ->value('weight_kg');

        $daysCount = count($days);
        $avgIntake = $daysCount > 0 ? $totalIntake / $daysCount : 0;
        $avgDeficit = $budget > 0 ? $budget - $avgIntake : 0;

        $totalCalories = $totalProtein * 4 + $totalCarbs * 4 + $totalFat * 9;

        return [
            'type' => 'weekly',
            'period' => $start->format('Y-m-d'). ' ~ '.$end->format('Y-m-d'),
            'days_count' => $daysCount,
            'days_with_records' => $daysWithRecords,
            'completion_rate' => $daysCount > 0 ? round($daysWithRecords / $daysCount * 100, 1) : 0,
            'daily_stats' => $dailyStats,
            'total_intake' => round($totalIntake, 1),
            'total_burned' => round($totalBurned, 1),
            'avg_intake' => round($avgIntake, 1),
            'avg_deficit' => round($avgDeficit, 1),
            'avg_burned' => $daysCount > 0 ? round($totalBurned / $daysCount, 1) : 0,
            'budget' => $budget,
            'weight_start' => $startWeight ? round((float) $startWeight, 1) : null,
            'weight_end' => $endWeight ? round((float) $endWeight, 1) : null,
            'weight_change' => ($startWeight && $endWeight) ? round((float) $endWeight - (float) $startWeight, 1) : null,
            'protein_total' => round($totalProtein, 1),
            'carbs_total' => round($totalCarbs, 1),
            'fat_total' => round($totalFat, 1),
            'protein_pct' => $totalCalories > 0 ? round($totalProtein * 4 / $totalCalories * 100, 1) : 0,
            'carbs_pct' => $totalCalories > 0 ? round($totalCarbs * 4 / $totalCalories * 100, 1) : 0,
            'fat_pct' => $totalCalories > 0 ? round($totalFat * 9 / $totalCalories * 100, 1) : 0,
            'healthy_loss_days' => $this->countHealthyLossDays($dailyStats, $budget),
        ];
    }

    public function generateMonthlyReport(int $userId, string $month): array
    {
        $start = Carbon::parse($month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $weeks = [];
        $weekStart = $start->copy()->startOfWeek();

        while ($weekStart->lte($end)) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            if ($weekEnd->gt($end)) {
                $weekEnd = $end->copy();
            }
            $weeks[] = $this->generateWeeklyReport($userId, $weekStart->toDateString());
            $weekStart->addWeek();
        }

        // Aggregate weekly stats
        $totalDays = 0;
        $totalDaysWithRecords = 0;
        $totalIntake = 0;
        $totalBurned = 0;
        $avgDeficit = 0;

        foreach ($weeks as $week) {
            $totalDays += $week['days_count'];
            $totalDaysWithRecords += $week['days_with_records'];
            $totalIntake += $week['total_intake'];
            $totalBurned += $week['total_burned'];
            $avgDeficit += $week['avg_deficit'];
        }

        $weeksCount = count($weeks);
        $avgDeficitMonthly = $weeksCount > 0 ? round($avgDeficit / $weeksCount, 1) : 0;

        // Weight change across month
        $startWeight = WeightRecord::where('user_id', $userId)
            ->whereDate('date', '<=', $start->toDateString())
            ->latest('date')
            ->value('weight_kg');

        $endWeight = WeightRecord::where('user_id', $userId)
            ->whereDate('date', '<=', $end->toDateString())
            ->latest('date')
            ->value('weight_kg');

        return [
            'type' => 'monthly',
            'period' => $start->format('Y-m-d'). ' ~ '.$end->format('Y-m-d'),
            'weeks' => $weeks,
            'weeks_count' => $weeksCount,
            'total_days' => $totalDays,
            'total_days_with_records' => $totalDaysWithRecords,
            'completion_rate' => $totalDays > 0 ? round($totalDaysWithRecords / $totalDays * 100, 1) : 0,
            'total_intake' => round($totalIntake, 1),
            'total_burned' => round($totalBurned, 1),
            'avg_deficit' => $avgDeficitMonthly,
            'weight_start' => $startWeight ? round((float) $startWeight, 1) : null,
            'weight_end' => $endWeight ? round((float) $endWeight, 1) : null,
            'weight_change' => ($startWeight && $endWeight) ? round((float) $endWeight - (float) $startWeight, 1) : null,
        ];
    }

    public function getReportHistory(int $userId): array
    {
        // Generate available report dates from existing data
        $earliestDate = MealRecord::where('user_id', $userId)
            ->min('date');

        if (! $earliestDate) {
            return ['weekly' => [], 'monthly' => []];
        }

        $start = Carbon::parse($earliestDate);
        $now = Carbon::now();

        $weeklyReports = [];
        $monthlyReports = [];

        $current = $start->copy()->startOfWeek();
        while ($current->lte($now)) {
            $weeklyReports[] = [
                'period' => $current->format('Y-m-d'),
                'label' => $current->format('m/d').' ~ '.$current->copy()->endOfWeek()->format('m/d'),
            ];
            $current->addWeek();
        }

        $current = $start->copy()->startOfMonth();
        while ($current->lte($now)) {
            $monthlyReports[] = [
                'period' => $current->format('Y-m'),
                'label' => $current->format('Y年m月'),
            ];
            $current->addMonth();
        }

        return [
            'weekly' => array_reverse($weeklyReports),
            'monthly' => array_reverse($monthlyReports),
        ];
    }

    private function countHealthyLossDays(array $dailyStats, float $budget): int
    {
        if ($budget <= 0) {
            return 0;
        }

        $count = 0;
        foreach ($dailyStats as $day) {
            $deficit = $budget - $day['intake'];
            // Healthy deficit: 300-750 kcal
            if ($deficit >= 300 && $deficit <= 750) {
                $count++;
            }
        }

        return $count;
    }
}
