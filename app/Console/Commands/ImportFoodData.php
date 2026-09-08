<?php

namespace App\Console\Commands;

use App\Models\FoodItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportFoodData extends Command
{
    protected $signature = 'food:import
                            {--file= : Path to CSV or JSON file}
                            {--format=csv : File format (csv or json)}
                            {--source=crawled : Source tag for imported items}
                            {--dry-run : Preview without inserting}';

    protected $description = 'Import food data from CSV or JSON file with deduplication and validation';

    private int $imported = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $file = $this->option('file');
        $format = $this->option('format');
        $source = $this->option('source');
        $dryRun = $this->option('dry-run');

        if (! $file || ! file_exists($file)) {
            $this->error('File not found: ' . ($file ?: 'none specified'));

            return self::FAILURE;
        }

        $this->info("Importing food data from {$file} (format: {$format})");
        if ($dryRun) {
            $this->warn('DRY RUN MODE — no data will be inserted.');
        }

        $items = match ($format) {
            'json' => $this->parseJson($file),
            'csv' => $this->parseCsv($file),
            default => $this->handleUnsupportedFormat($format),
        };

        if (empty($items)) {
            $this->warn('No items found in file.');

            return self::SUCCESS;
        }

        $this->info('Found ' . count($items) . ' items to process.');
        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            $bar->advance();
            $this->processItem($item, $source, $dryRun);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import complete:");
        $this->info("  Imported: {$this->imported}");
        $this->info("  Skipped (duplicate): {$this->skipped}");
        $this->info("  Failed (validation): {$this->failed}");

        return self::SUCCESS;
    }

    private function processItem(array $item, string $source, bool $dryRun): void
    {
        // Validate required fields
        $validated = $this->validateItem($item);
        if ($validated === null) {
            $this->failed++;
            return;
        }

        // Check for duplicates (fuzzy match by name)
        if ($this->isDuplicate($validated['name'], $validated['aliases'] ?? [])) {
            $this->skipped++;
            return;
        }

        if (! $dryRun) {
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
                'source' => $source,
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
                $this->newLine();
                $this->warn("  Skipped: missing field '{$field}' for '" . ($item['name'] ?? 'unknown') . "'");

                return null;
            }
        }

        // Validate numeric ranges
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

        // Calorie verification: check if macro-based calories match within 20%
        $macroCalories = ($item['protein_per_100g'] * 4) + ($item['carbs_per_100g'] * 4) + ($item['fat_per_100g'] * 9);
        if ($macroCalories > 0 && abs($macroCalories - $item['calories_per_100g']) / $macroCalories > 0.20) {
            $this->newLine();
            $this->warn("  Warning: calorie mismatch for '" . $item['name'] . "' (declared: {$item['calories_per_100g']}, calculated: " . round($macroCalories, 1) . ")");
        }

        // Normalize aliases
        if (isset($item['aliases']) && is_string($item['aliases'])) {
            $item['aliases'] = array_map('trim', explode(',', $item['aliases']));
        }

        return $item;
    }

    private function isDuplicate(string $name, array $aliases): bool
    {
        $normalizedName = Str::lower(trim($name));

        // Exact match
        if (FoodItem::whereRaw('LOWER(name) = ?', [$normalizedName])->exists()) {
            return true;
        }

        // Check aliases
        foreach ($aliases as $alias) {
            if (FoodItem::whereRaw('LOWER(name) = ?', [Str::lower(trim($alias))])->exists()) {
                return true;
            }
        }

        // Fuzzy match: check if any existing name contains this name or vice versa
        $existing = FoodItem::select('name')->limit(500)->pluck('name');
        foreach ($existing as $existingName) {
            if (similar_text(Str::lower($normalizedName), Str::lower($existingName)) / max(strlen($normalizedName), strlen($existingName)) > 0.85) {
                return true;
            }
        }

        return false;
    }

    private function parseJson(string $file): array
    {
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return [];
        }

        // Support both array of objects and object with "foods" key
        return $data['foods'] ?? $data;
    }

    private function parseCsv(string $file): array
    {
        $handle = fopen($file, 'r');
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

    private function handleUnsupportedFormat(string $format): array
    {
        $this->error("Unsupported format: {$format}");

        return [];
    }
}
