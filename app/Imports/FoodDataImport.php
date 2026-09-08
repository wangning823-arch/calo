<?php

namespace App\Imports;

use App\Models\FoodItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FoodDataImport
{
    private int $imported = 0;

    private int $skipped = 0;

    private int $failed = 0;

    private string $source;

    private bool $dryRun;

    public function __construct(string $source = 'crawled', bool $dryRun = false)
    {
        $this->source = $source;
        $this->dryRun = $dryRun;
    }

    public function import(string $filePath, string $format): void
    {
        $items = match ($format) {
            'json' => $this->parseJson($filePath),
            'csv' => $this->parseCsv($filePath),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    private function processItem(array $item): void
    {
        $validated = $this->validateItem($item);
        if ($validated === null) {
            $this->failed++;
            return;
        }

        if ($this->isDuplicate($validated['name'], $validated['aliases'] ?? [])) {
            $this->skipped++;
            return;
        }

        if (! $this->dryRun) {
            FoodItem::create([
                'name' => $validated['name'],
                'aliases' => $validated['aliases'] ?? null,
                'category' => $validated['category'],
                'calories_per_100g' => $validated['calories_per_100g'],
                'protein_per_100g' => $validated['protein_per_100g'],
                'carbs_per_100g' => $validated['carbs_per_100g'],
                'fat_per_100g' => $validated['fat_per_100g'],
                'serving_size' => $validated['serving_size'] ?? 100,
                'serving_unit' => $validated['serving_unit'] ?? 'g',
                'source' => $this->source,
                'review_status' => 'approved',
                'is_user_custom' => false,
                'version' => 1,
                'source_url' => $validated['source_url'] ?? null,
            ]);
        }

        $this->imported++;
    }

    private function validateItem(array $item): ?array
    {
        $required = ['name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'];

        foreach ($required as $field) {
            if (! isset($item[$field]) || $item[$field] === '') {
                return null;
            }
        }

        if ($item['calories_per_100g'] < 0 || $item['calories_per_100g'] > 1000) {
            return null;
        }
        if ($item['protein_per_100g'] < 0 || $item['protein_per_100g'] > 100) {
            return null;
        }
        if ($item['carbs_per_100g'] < 0 || $item['carbs_per_100g'] > 100) {
            return null;
        }
        if ($item['fat_per_100g'] < 0 || $item['fat_per_100g'] > 100) {
            return null;
        }

        if (isset($item['aliases']) && is_string($item['aliases'])) {
            $item['aliases'] = array_map('trim', explode(',', $item['aliases']));
        }

        return $item;
    }

    private function isDuplicate(string $name, array $aliases): bool
    {
        $normalizedName = Str::lower(trim($name));

        if (FoodItem::whereRaw('LOWER(name) = ?', [$normalizedName])->exists()) {
            return true;
        }

        foreach ($aliases as $alias) {
            if (FoodItem::whereRaw('LOWER(name) = ?', [Str::lower(trim($alias))])->exists()) {
                return true;
            }
        }

        $existing = FoodItem::select('name')->limit(500)->pluck('name');
        foreach ($existing as $existingName) {
            if (similar_text(Str::lower($normalizedName), Str::lower($existingName)) / max(strlen($normalizedName), strlen($existingName)) > 0.85) {
                return true;
            }
        }

        return false;
    }

    private function parseJson(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return [];
        }

        return $data['foods'] ?? $data;
    }

    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return [];
        }

        $headers = array_map('trim', $headers);
        $items = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $items[] = array_combine($headers, $row);
            }
        }

        fclose($handle);

        return $items;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getFailedCount(): int
    {
        return $this->failed;
    }
}
