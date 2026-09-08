<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Models\User;
use Carbon\Carbon;

class ExerciseService
{
    public function recordExercise(User $user, array $data): ExerciseRecord
    {
        $exerciseType = ExerciseType::findOrFail($data['exercise_type_id']);
        $weightKg = $this->getUserWeight($user);

        $estimatedCalories = $this->calculateCalories(
            (float) $exerciseType->met_value,
            $weightKg,
            (int) $data['duration_minutes']
        );

        return ExerciseRecord::create([
            'user_id' => $user->id,
            'date' => $data['date'] ?? now()->toDateString(),
            'exercise_type_id' => $exerciseType->id,
            'duration_minutes' => $data['duration_minutes'],
            'intensity' => $data['intensity'] ?? $this->guessIntensity($exerciseType->met_value),
            'estimated_calories' => round($estimatedCalories, 1),
            'distance_km' => $data['distance_km'] ?? null,
        ]);
    }

    public function calculateCalories(float $met, float $weightKg, int $durationMinutes): float
    {
        $durationHours = $durationMinutes / 60;

        // Net calorie expenditure: (MET - 1) × weight(kg) × duration(h)
        return max(0, ($met - 1) * $weightKg * $durationHours);
    }

    public function guessIntensity(float $met): string
    {
        if ($met < 4.0) {
            return 'light';
        } elseif ($met < 6.0) {
            return 'moderate';
        }

        return 'heavy';
    }

    public function getDailyExerciseSummary(User $user, string $date): array
    {
        $records = ExerciseRecord::where('user_id', $user->id)
            ->where('date', $date)
            ->with('exerciseType')
            ->get();

        return [
            'total_calories' => $records->sum('estimated_calories'),
            'total_minutes' => $records->sum('duration_minutes'),
            'record_count' => $records->count(),
            'records' => $records,
        ];
    }

    public function updateRecord(ExerciseRecord $record, array $data): ExerciseRecord
    {
        $exerciseType = isset($data['exercise_type_id'])
            ? ExerciseType::findOrFail($data['exercise_type_id'])
            : $record->exerciseType;

        $user = $record->user;
        $weightKg = $this->getUserWeight($user);
        $duration = $data['duration_minutes'] ?? $record->duration_minutes;

        $estimatedCalories = $this->calculateCalories(
            (float) $exerciseType->met_value,
            $weightKg,
            (int) $duration
        );

        $record->update([
            'exercise_type_id' => $exerciseType->id,
            'duration_minutes' => $duration,
            'intensity' => $data['intensity'] ?? $this->guessIntensity($exerciseType->met_value),
            'estimated_calories' => round($estimatedCalories, 1),
            'distance_km' => $data['distance_km'] ?? $record->distance_km,
        ]);

        return $record->fresh();
    }

    public function deleteRecord(ExerciseRecord $record): bool
    {
        return $record->delete();
    }

    private function getUserWeight(User $user): float
    {
        $latestWeight = $user->weightRecords()->latest('date')->first();

        return $latestWeight ? (float) $latestWeight->weight_kg : 70.0;
    }
}
