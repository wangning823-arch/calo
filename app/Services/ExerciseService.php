<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseService
{
    /** 常见分类优先，避免「其他」里的冷门项目排在最前 */
    private const CATEGORY_PRIORITY = [
        '有氧' => 0,
        '力量' => 1,
        '柔韧' => 2,
        '日常' => 3,
        '球类' => 4,
        '水上' => 5,
        '冬季' => 6,
        '其他' => 7,
    ];

    private const FREQUENT_WINDOW_DAYS = 90;

    public function recordExercise(User $user, array $data): ExerciseRecord
    {
        $exerciseType = ExerciseType::findOrFail($data['exercise_type_id']);
        $weightKg = $this->getUserWeight($user);

        $estimatedCalories = $this->calculateCalories(
            (float) $exerciseType->met_value,
            $weightKg,
            (int) $data['duration_minutes']
        );

        $recordedAt = $data['recorded_at'] ?? now()->toDateTimeString();
        $date = $data['date'] ?? Carbon::parse($recordedAt)->toDateString();

        return ExerciseRecord::create([
            'user_id' => $user->id,
            'date' => $date,
            'recorded_at' => $recordedAt,
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

        $updateData = [
            'exercise_type_id' => $exerciseType->id,
            'duration_minutes' => $duration,
            'intensity' => $data['intensity'] ?? $this->guessIntensity($exerciseType->met_value),
            'estimated_calories' => round($estimatedCalories, 1),
            'distance_km' => $data['distance_km'] ?? $record->distance_km,
        ];

        if (isset($data['recorded_at'])) {
            $updateData['recorded_at'] = $data['recorded_at'];
            $updateData['date'] = Carbon::parse($data['recorded_at'])->toDateString();
        }

        $record->update($updateData);

        return $record->fresh();
    }

    public function deleteRecord(ExerciseRecord $record): bool
    {
        return $record->delete();
    }

    /**
     * 运动类型列表：用户近期常用的靠前，其余按常见分类与名称排序。
     */
    public function getExerciseTypesForUser(User $user): Collection
    {
        $allTypes = ExerciseType::orderBy('category')->orderBy('name')->get();
        $frequentIds = $this->getFrequentExerciseTypeIds($user);

        if ($frequentIds->isEmpty()) {
            return $this->sortTypes($allTypes);
        }

        $frequent = $frequentIds
            ->map(fn (int $id) => $allTypes->firstWhere('id', $id))
            ->filter()
            ->values();

        $rest = $this->sortTypes(
            $allTypes->whereNotIn('id', $frequent->pluck('id'))
        );

        return $frequent->concat($rest)->values();
    }

    /**
     * @return Collection<int> 依使用频率降序的 exercise_type_id
     */
    public function getFrequentExerciseTypeIds(User $user): Collection
    {
        return ExerciseRecord::query()
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(self::FREQUENT_WINDOW_DAYS)->toDateString())
            ->groupBy('exercise_type_id')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->orderByDesc(DB::raw('MAX(date)'))
            ->pluck('exercise_type_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    /** @return Collection<string> 分类页签顺序：常见分类靠前 */
    public function getOrderedCategories(): Collection
    {
        return ExerciseType::pluck('category')
            ->unique()
            ->sortBy(fn (string $cat) => $this->categoryRank($cat))
            ->values();
    }

    private function sortTypes(Collection $types): Collection
    {
        return $types->sortBy([
            fn (ExerciseType $a, ExerciseType $b) => $this->categoryRank($a->category) <=> $this->categoryRank($b->category),
            fn (ExerciseType $a, ExerciseType $b) => $a->name <=> $b->name,
        ])->values();
    }

    private function categoryRank(string $category): int
    {
        return self::CATEGORY_PRIORITY[$category] ?? 99;
    }

    private function getUserWeight(User $user): float
    {
        $latestWeight = $user->weightRecords()->latest('date')->first();

        return $latestWeight ? (float) $latestWeight->weight_kg : 70.0;
    }
}
