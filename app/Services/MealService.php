<?php

namespace App\Services;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MealService
{
    private const VALID_MEAL_TYPES = ['breakfast', 'lunch', 'dinner', 'snack'];
    private const MAX_SERVING_GRAMS = 5000;
    private const MIN_SERVING_GRAMS = 1;
    private const EDIT_WINDOW_DAYS = 30;

    public function recordMeal(int $userId, array $data): MealRecord
    {
        $food = FoodItem::findOrFail($data['food_id']);
        $servingGrams = (float) ($data['serving_grams'] ?? 100);
        $servingGrams = max(self::MIN_SERVING_GRAMS, min(self::MAX_SERVING_GRAMS, $servingGrams));

        $calculatedCalories = round($food->calories_per_100g * $servingGrams / 100, 1);

        $recordedAt = $data['recorded_at'] ?? Carbon::now()->toDateTimeString();
        $date = $data['date'] ?? Carbon::parse($recordedAt)->toDateString();

        return MealRecord::create([
            'user_id' => $userId,
            'date' => $date,
            'recorded_at' => $recordedAt,
            'timezone' => $data['timezone'] ?? 'Asia/Shanghai',
            'meal_type' => $data['meal_type'],
            'food_id' => $food->id,
            'food_version' => $food->version,
            'serving_grams' => $servingGrams,
            'calculated_calories' => $calculatedCalories,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getDailySummary(int $userId, string $date): array
    {
        $records = MealRecord::where('user_id', $userId)
            ->whereDate('date', $date)
            ->with('food')
            ->get();

        $totalCalories = 0;
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        foreach ($records as $record) {
            $food = $record->food;
            if (! $food) {
                continue;
            }

            $factor = $record->serving_grams / 100;
            $totalCalories += $food->calories_per_100g * $factor;
            $totalProtein += $food->protein_per_100g * $factor;
            $totalCarbs += $food->carbs_per_100g * $factor;
            $totalFat += $food->fat_per_100g * $factor;
        }

        return [
            'total_calories' => round($totalCalories, 1),
            'total_protein' => round($totalProtein, 1),
            'total_carbs' => round($totalCarbs, 1),
            'total_fat' => round($totalFat, 1),
            'record_count' => $records->count(),
            'by_meal' => $records->groupBy('meal_type')->map(function ($mealRecords) {
                $totalCal = 0;
                foreach ($mealRecords as $r) {
                    $food = $r->food;
                    if ($food) {
                        $totalCal += $food->calories_per_100g * $r->serving_grams / 100;
                    }
                }
                return [
                    'calories' => round($totalCal, 1),
                    'count' => $mealRecords->count(),
                ];
            }),
        ];
    }

    public function updateRecord(int $recordId, array $data, int $userId): MealRecord
    {
        $record = MealRecord::where('id', $recordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($record->date->diffInDays(now()) > self::EDIT_WINDOW_DAYS) {
            abort(403, '只能编辑30天内的记录。');
        }

        if (isset($data['serving_grams'])) {
            $data['serving_grams'] = max(self::MIN_SERVING_GRAMS, min(self::MAX_SERVING_GRAMS, (float) $data['serving_grams']));
        }

        if (isset($data['food_id'])) {
            $food = FoodItem::findOrFail($data['food_id']);
            $data['food_version'] = $food->version;
            $data['calculated_calories'] = round($food->calories_per_100g * ($data['serving_grams'] ?? $record->serving_grams) / 100, 1);
        } elseif (isset($data['serving_grams'])) {
            $food = $record->food;
            $data['calculated_calories'] = round($food->calories_per_100g * $data['serving_grams'] / 100, 1);
        }

        // Update date from recorded_at if provided
        if (isset($data['recorded_at'])) {
            $data['date'] = Carbon::parse($data['recorded_at'])->toDateString();
        }

        $record->update($data);

        return $record->fresh();
    }

    public function deleteRecord(int $recordId, int $userId): bool
    {
        $record = MealRecord::where('id', $recordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($record->date->diffInDays(now()) > self::EDIT_WINDOW_DAYS) {
            abort(403, '只能删除30天内的记录。');
        }

        return $record->delete();
    }

    public function copyYesterdayMeal(int $userId, string $mealType): ?MealRecord
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $yesterdayRecord = MealRecord::where('user_id', $userId)
            ->whereDate('date', $yesterday)
            ->where('meal_type', $mealType)
            ->latest()
            ->first();

        if (! $yesterdayRecord) {
            return null;
        }

        return $this->recordMeal($userId, [
            'food_id' => $yesterdayRecord->food_id,
            'meal_type' => $mealType,
            'serving_grams' => $yesterdayRecord->serving_grams,
            'date' => Carbon::now()->toDateString(),
            'recorded_at' => Carbon::now()->toDateTimeString(),
            'notes' => '复制自昨日',
        ]);
    }

    public function getRecentRecords(int $userId, int $days = 30): Collection
    {
        return MealRecord::where('user_id', $userId)
            ->whereDate('date', '>=', Carbon::now()->subDays($days)->toDateString())
            ->with('food')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();
    }
}
