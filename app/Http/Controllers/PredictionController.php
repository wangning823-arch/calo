<?php

namespace App\Http\Controllers;

use App\Models\ExerciseRecord;
use App\Models\MealRecord;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        private PredictionService $predictionService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $prediction = $this->predictionService->predictGoalAchievement($user);
        $plateau = $this->predictionService->detectPlateau($user);
        $progress = $this->predictionService->getGoalProgress($user);

        return view('predictions.index', compact('user', 'prediction', 'plateau', 'progress'));
    }

    public function api(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'prediction' => $this->predictionService->predictGoalAchievement($user),
            'plateau' => $this->predictionService->detectPlateau($user),
            'progress' => $this->predictionService->getGoalProgress($user),
        ]);
    }

    public function calendarApi(Request $request, int $year, int $month): JsonResponse
    {
        $user = $request->user();
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $goal = $user->activeWeightGoal;
        $budget = $goal ? (float) $goal->daily_calorie_budget : 0;

        // Get all meals for the month
        $meals = MealRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('food')
            ->get()
            ->groupBy(fn($m) => $m->date->toDateString());

        // Get all exercises for the month
        $exercises = ExerciseRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn($e) => $e->date->toDateString());

        $days = [];
        $current = $start->copy();
        $totalDaysInMonth = $start->daysInMonth;

        while ($current->lte($end)) {
            $dayStr = $current->toDateString();
            $dayMeals = $meals->get($dayStr, collect());
            $dayExercises = $exercises->get($dayStr, collect());
            $hasRecords = $dayMeals->isNotEmpty() || $dayExercises->isNotEmpty();

            $intake = 0;
            $burned = 0;
            $exerciseIcons = [];

            if ($hasRecords) {
                foreach ($dayMeals as $meal) {
                    if ($meal->food) {
                        $factor = $meal->serving_grams / 100;
                        $intake += $meal->food->calories_per_100g * $factor;
                    }
                }

                $burned = (float) $dayExercises->sum('estimated_calories');

                foreach ($dayExercises as $ex) {
                    $type = $ex->exerciseType;
                    if ($type) {
                        $distance = $ex->distance_km ? round((float) $ex->distance_km) : null;
                        if ($distance && in_array($distance, [5, 10])) {
                            $exerciseIcons[] = (string) $distance;
                        } elseif (stripos($type->category, '力量') !== false || stripos($type->name, '力量') !== false) {
                            $exerciseIcons[] = '💪';
                        } elseif ($distance) {
                            $exerciseIcons[] = (string) $distance;
                        }
                    }
                }
                $exerciseIcons = array_unique($exerciseIcons);
            }

            $deficit = ($hasRecords && $budget > 0) ? round($budget - $intake + $burned, 0) : null;

            $days[] = [
                'date' => $dayStr,
                'day' => (int) $current->format('d'),
                'has_records' => $hasRecords,
                'intake' => $hasRecords ? round($intake) : null,
                'burned' => $hasRecords ? round($burned) : null,
                'deficit' => $deficit,
                'exercise_icons' => $exerciseIcons,
            ];

            $current->addDay();
        }

        $recordedDays = collect($days)->where('has_records', true);
        $recordDays = $recordedDays->count();
        $exerciseDays = $recordedDays->filter(fn($d) => count($d['exercise_icons']) > 0)->count();
        $totalIntake = $recordedDays->sum('intake');
        $totalBurned = $recordedDays->sum('burned');

        return response()->json([
            'year' => $year,
            'month' => $month,
            'month_label' => $start->format('Y年n月'),
            'days' => $days,
            'budget' => $budget,
            'summary' => [
                'total_intake' => round($totalIntake),
                'total_burned' => round($totalBurned),
                'avg_intake' => $recordDays > 0 ? round($totalIntake / $recordDays) : 0,
                'avg_deficit' => ($budget > 0 && $recordDays > 0) ? round($budget - $totalIntake / $recordDays + $totalBurned / $recordDays) : null,
                'record_days' => $recordDays,
                'total_days' => $totalDaysInMonth,
                'exercise_days' => $exerciseDays,
                'completion_rate' => $totalDaysInMonth > 0 ? round($recordDays / $totalDaysInMonth * 100) : 0,
            ],
        ]);
    }
}
