<?php

namespace App\Console\Commands;

use App\Models\FoodItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CrawlFromSource extends Command
{
    protected $signature = 'food:crawl
                            {--source= : Source URL or identifier}
                            {--limit=100 : Maximum items to crawl}
                            {--resume : Resume from last checkpoint}
                            {--output= : Output file path for crawled data}';

    protected $description = 'Crawl food nutrition data from public sources with rate limiting';

    private int $crawled = 0;
    private int $failed = 0;
    private float $requestDelay = 1.0; // 1 second between requests

    private string $userAgent = 'CaloApp/1.0 (Nutrition Data Crawler; +https://calo.app)';

    private string $checkpointFile;

    public function handle(): int
    {
        $source = $this->option('source');
        $limit = (int) $this->option('limit');
        $resume = $this->option('resume');
        $output = $this->option('output');

        $this->checkpointFile = storage_path('app/crawl_checkpoint.json');

        if (! $source) {
            $this->error('Please specify a source with --source=<url>');

            return self::FAILURE;
        }

        $this->info("Crawling food data from: {$source}");
        $this->info("Limit: {$limit} items");
        $this->info("Rate limit: 1 request per {$this->requestDelay}s");

        $offset = 0;
        if ($resume && file_exists($this->checkpointFile)) {
            $checkpoint = json_decode(file_get_contents($this->checkpointFile), true);
            $offset = $checkpoint['offset'] ?? 0;
            $this->info("Resuming from offset: {$offset}");
        }

        $results = [];

        try {
            $results = $this->crawlSource($source, $limit, $offset);
        } catch (\Exception $e) {
            $this->error("Crawl error: {$e->getMessage()}");

            // Save checkpoint on error
            $this->saveCheckpoint($source, $offset + $this->crawled);

            return self::FAILURE;
        }

        if ($output) {
            $this->saveResults($results, $output);
        }

        $this->info("\nCrawl complete:");
        $this->info("  Crawled: {$this->crawled}");
        $this->info("  Failed: {$this->failed}");

        return self::SUCCESS;
    }

    private function crawlSource(string $source, int $limit, int $offset): array
    {
        $results = [];
        $page = (int) ($offset / 50) + 1;

        while ($this->crawled < $limit) {
            $url = $this->buildPageUrl($source, $page);

            $this->info("Fetching page {$page}...");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout(30)->get($url);

                if ($response->failed()) {
                    $this->warn("Failed to fetch {$url}: HTTP {$response->status()}");
                    $this->failed++;
                    break;
                }

                $items = $this->parseResponse($response->body());

                if (empty($items)) {
                    $this->info("No more items found at page {$page}.");
                    break;
                }

                foreach ($items as $item) {
                    if ($this->crawled >= $limit) {
                        break;
                    }

                    $validated = $this->validateAndClean($item);
                    if ($validated) {
                        $results[] = $validated;
                        $this->crawled++;
                    } else {
                        $this->failed++;
                    }
                }

                $page++;
                $this->saveCheckpoint($source, $offset + $this->crawled);

                // Rate limiting
                usleep((int) ($this->requestDelay * 1_000_000));

            } catch (\Exception $e) {
                $this->warn("Error on page {$page}: {$e->getMessage()}");
                $this->failed++;
                break;
            }
        }

        return $results;
    }

    private function buildPageUrl(string $source, int $page): string
    {
        // Generic pagination support
        $separator = str_contains($source, '?') ? '&' : '?';

        return "{$source}{$separator}page={$page}";
    }

    private function parseResponse(string $body): array
    {
        // Try JSON first
        $data = json_decode($body, true);
        if (is_array($data)) {
            return $data['data'] ?? $data['items'] ?? $data;
        }

        // Try to parse as HTML (basic)
        // This is a simplified parser - production would use DOMDocument
        return [];
    }

    private function validateAndClean(array $item): ?array
    {
        $required = ['name', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'];

        foreach ($required as $field) {
            if (! isset($item[$field]) || $item[$field] === '') {
                return null;
            }
        }

        // Ensure numeric values
        foreach (['calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'] as $field) {
            $item[$field] = (float) $item[$field];
            if ($item[$field] < 0) {
                return null;
            }
        }

        // Auto-categorize if missing
        if (! isset($item['category']) || $item['category'] === '') {
            $item['category'] = $this->guessCategory($item['name']);
        }

        // Normalize name
        $item['name'] = Str::title(trim($item['name']));

        // Set defaults
        $item['serving_size'] = $item['serving_size'] ?? 100;
        $item['serving_unit'] = $item['serving_unit'] ?? 'g';
        $item['source'] = 'crawled';
        $item['review_status'] = 'pending';
        $item['is_user_custom'] = false;
        $item['version'] = 1;

        return $item;
    }

    private function guessCategory(string $name): string
    {
        $categories = [
            '主食' => ['饭', '面', '馒头', '饼', '粥', '粉', '年糕', '饺子', '包子', '面包', '吐司', '麦片'],
            '蔬菜' => ['菜', '白菜', '菠菜', '茄子', '豆角', '黄瓜', '番茄', '土豆', '萝卜', '青椒', '芹菜'],
            '水果' => ['苹果', '香蕉', '橙子', '葡萄', '西瓜', '桃', '梨', '芒果', '草莓', '蓝莓', '猕猴桃'],
            '肉类' => ['肉', '鸡', '鸭', '猪', '牛', '羊', '排骨', '五花', '里脊', '腿肉'],
            '蛋奶' => ['蛋', '牛奶', '酸奶', '奶酪', '芝士'],
            '海鲜' => ['鱼', '虾', '蟹', '贝', '鱿鱼', '三文鱼', '金枪鱼'],
            '零食' => ['饼干', '薯片', '巧克力', '糖果', '坚果', '瓜子', '花生'],
            '饮料' => ['水', '茶', '咖啡', '果汁', '可乐', '啤酒'],
            '调味品' => ['盐', '糖', '酱油', '醋', '油', '酱', '辣椒', '花椒'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($name, $keyword)) {
                    return $category;
                }
            }
        }

        return '其他';
    }

    private function saveCheckpoint(string $source, int $offset): void
    {
        file_put_contents($this->checkpointFile, json_encode([
            'source' => $source,
            'offset' => $offset,
            'timestamp' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));
    }

    private function saveResults(array $results, string $output): void
    {
        $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($output, $json);
        $this->info("Results saved to: {$output}");
    }
}
