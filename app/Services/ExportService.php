<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportService
{
    public function exportReport(int $userId, string $type, string $dateRange): string
    {
        $user = User::findOrFail($userId);
        $data = $this->getExportData($user, $type, $dateRange);

        $filename = "report_{$type}_{$dateRange}_".Carbon::now()->timestamp.'.csv';
        $path = "exports/{$filename}";

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_keys($data[0] ?? []));
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($path, $csv);

        return $path;
    }

    public function requestFullDataExport(int $userId): string
    {
        $user = User::findOrFail($userId);
        $token = bin2hex(random_bytes(32));

        $exportData = [
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'height' => $user->height,
                'activity_level' => $user->activity_level,
                'unit_preference' => $user->unit_preference,
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'weight_goals' => $user->weightGoals()->get()->map(fn ($g) => [
                'mode' => $g->mode,
                'start_weight' => $g->start_weight,
                'target_weight' => $g->target_weight,
                'target_date' => $g->target_date?->format('Y-m-d'),
                'daily_calorie_budget' => $g->daily_calorie_budget,
                'status' => $g->status,
                'created_at' => $g->created_at->toIso8601String(),
            ])->toArray(),
            'meal_records' => $user->mealRecords()->with('food')->get()->map(fn ($m) => [
                'date' => $m->date->format('Y-m-d'),
                'meal_type' => $m->meal_type,
                'food_name' => $m->food?->name,
                'serving_grams' => $m->serving_grams,
                'calculated_calories' => $m->calculated_calories,
                'notes' => $m->notes,
                'created_at' => $m->created_at->toIso8601String(),
            ])->toArray(),
            'exercise_records' => $user->exerciseRecords()->get()->map(fn ($e) => [
                'date' => $e->date->format('Y-m-d'),
                'exercise_type_id' => $e->exercise_type_id,
                'duration_minutes' => $e->duration_minutes,
                'intensity' => $e->intensity,
                'estimated_calories' => $e->estimated_calories,
                'distance_km' => $e->distance_km,
                'created_at' => $e->created_at->toIso8601String(),
            ])->toArray(),
            'weight_records' => $user->weightRecords()->get()->map(fn ($w) => [
                'date' => $w->date->format('Y-m-d'),
                'weight_kg' => $w->weight_kg,
                'body_fat_percentage' => $w->body_fat_percentage,
                'waist_cm' => $w->waist_cm,
                'hip_cm' => $w->hip_cm,
                'created_at' => $w->created_at->toIso8601String(),
            ])->toArray(),
            'exported_at' => Carbon::now()->toIso8601String(),
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = "full_export_{$token}.json";
        $path = "exports/{$filename}";

        Storage::disk('local')->put($path, $json);

        return $token;
    }

    public function getExportPath(string $token): ?string
    {
        $files = Storage::disk('local')->files('exports');
        foreach ($files as $file) {
            if (str_contains($file, $token)) {
                return $file;
            }
        }

        return null;
    }

    public function isExportExpired(string $path): bool
    {
        $filename = basename($path);
        preg_match('/_(\d+)\./', $filename, $matches);

        // Extract timestamp from filename or use file modification time
        $fullPath = Storage::disk('local')->path($path);
        if (! file_exists($fullPath)) {
            return true;
        }

        $modifiedAt = Carbon::createFromTimestamp(filemtime($fullPath));

        return $modifiedAt->diffInDays(Carbon::now()) > 7;
    }

    private function getExportData(User $user, string $type, string $dateRange): array
    {
        $dates = explode('..', $dateRange);
        $start = Carbon::parse($dates[0] ?? now()->subWeek());
        $end = Carbon::parse($dates[1] ?? now());

        if ($type === 'meals') {
            return $user->mealRecords()
                ->with('food')
                ->whereBetween('date', [$start, $end])
                ->get()
                ->map(fn ($m) => [
                    'date' => $m->date->format('Y-m-d'),
                    'meal_type' => $m->meal_type,
                    'food_name' => $m->food?->name,
                    'serving_grams' => $m->serving_grams,
                    'calories' => $m->calculated_calories,
                ])
                ->toArray();
        }

        if ($type === 'exercises') {
            return $user->exerciseRecords()
                ->whereBetween('date', [$start, $end])
                ->get()
                ->map(fn ($e) => [
                    'date' => $e->date->format('Y-m-d'),
                    'duration' => $e->duration_minutes,
                    'calories' => $e->estimated_calories,
                ])
                ->toArray();
        }

        if ($type === 'weights') {
            return $user->weightRecords()
                ->whereBetween('date', [$start, $end])
                ->get()
                ->map(fn ($w) => [
                    'date' => $w->date->format('Y-m-d'),
                    'weight_kg' => $w->weight_kg,
                ])
                ->toArray();
        }

        return [];
    }
}
