<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;

class WeightService
{
    private const KG_MIN = 10;
    private const KG_MAX = 150;

    public function recordWeight(User $user, array $data): WeightRecord
    {
        $weightKg = $this->toKg($data['weight'], $data['unit'] ?? null);

        $this->validateWeight($weightKg);

        // Same day: update the last record instead of creating new
        $dateStr = Carbon::parse($data['date'] ?? now()->toDateString())->toDateString();
        $existing = WeightRecord::where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->latest()
            ->first();

        if ($existing) {
            $existing->update([
                'weight_kg' => $weightKg,
                'body_fat_percentage' => $data['body_fat_percentage'] ?? $existing->body_fat_percentage,
                'waist_cm' => $data['waist_cm'] ?? $existing->waist_cm,
                'hip_cm' => $data['hip_cm'] ?? $existing->hip_cm,
            ]);

            return $existing->fresh();
        }

        return WeightRecord::create([
            'user_id' => $user->id,
            'date' => $data['date'] ?? now()->toDateString(),
            'weight_kg' => $weightKg,
            'body_fat_percentage' => $data['body_fat_percentage'] ?? null,
            'waist_cm' => $data['waist_cm'] ?? null,
            'hip_cm' => $data['hip_cm'] ?? null,
        ]);
    }

    public function validateWeight(float $weightKg): void
    {
        if ($weightKg < self::KG_MIN || $weightKg > self::KG_MAX) {
            throw new \InvalidArgumentException(
                "体重应在" . self::KG_MIN . "-" . self::KG_MAX . "kg之间。"
            );
        }
    }

    public function updateRecord(WeightRecord $record, array $data): WeightRecord
    {
        $weightKg = $this->toKg($data['weight'], $data['unit'] ?? null);

        $this->validateWeight($weightKg);

        $record->update([
            'weight_kg' => $weightKg,
            'body_fat_percentage' => $data['body_fat_percentage'] ?? $record->body_fat_percentage,
            'waist_cm' => $data['waist_cm'] ?? $record->waist_cm,
            'hip_cm' => $data['hip_cm'] ?? $record->hip_cm,
        ]);

        return $record->fresh();
    }

    public function deleteRecord(WeightRecord $record): bool
    {
        return $record->delete();
    }

    public function getWeightTrend(User $user, string $period = 'month'): array
    {
        $days = match ($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            default => 30,
        };

        $records = WeightRecord::where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subDays($days)->toDateString())
            ->orderBy('date')
            ->get();

        $movingAverage = $this->calculateMovingAverage($records, 7);

        return [
            'records' => $records->map(fn($r) => [
                'date' => $r->date->format('m/d'),
                'weight_kg' => (float) $r->weight_kg,
                'weight_display' => round((float) $r->weight_kg, 1),
            ])->values(),
            'moving_average' => $movingAverage,
            'period' => $period,
            'latest' => $records->last() ? [
                'weight_kg' => (float) $records->last()->weight_kg,
                'date' => $records->last()->date->format('m/d'),
            ] : null,
        ];
    }

    public function getLatestWeight(User $user): ?array
    {
        $record = WeightRecord::where('user_id', $user->id)
            ->latest('date')
            ->first();

        if (! $record) {
            return null;
        }

        return [
            'weight_kg' => (float) $record->weight_kg,
            'weight_display' => round((float) $record->weight_kg, 1),
            'unit' => 'kg',
            'date' => $record->date->format('Y-m-d'),
        ];
    }

    private function toKg(float $weight, ?string $unit): float
    {
        // Unit is kg-only; keep parameter for signature compatibility.
        return $weight;
    }

    private function calculateMovingAverage($records, int $window): array
    {
        $data = $records->pluck('weight_kg')->toArray();
        $result = [];

        for ($i = 0; $i < count($data); $i++) {
            $start = max(0, $i - $window + 1);
            $slice = array_slice($data, $start, $i - $start + 1);
            $result[] = round(array_sum($slice) / count($slice), 2);
        }

        return $result;
    }
}
