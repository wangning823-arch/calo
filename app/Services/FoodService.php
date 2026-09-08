<?php

namespace App\Services;

use App\Models\FavoriteFood;
use App\Models\FoodItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FoodService
{
    public function search(string $query, ?int $userId = null): Collection
    {
        if (empty($query)) {
            return collect();
        }

        $query = trim($query);

        $results = FoodItem::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('aliases', 'LIKE', "%{$query}%");
            })
            ->where('review_status', 'approved')
            ->limit(50)
            ->get();

        // Boost user's frequently eaten foods
        if ($userId && $results->isNotEmpty()) {
            $foodIds = $results->pluck('id');
            $frequentIds = DB::table('meal_records')
                ->where('user_id', $userId)
                ->whereIn('food_id', $foodIds)
                ->select('food_id', DB::raw('count(*) as cnt'))
                ->groupBy('food_id')
                ->orderByDesc('cnt')
                ->pluck('food_id');

            $results = $results->sortBy(function ($food) use ($frequentIds) {
                $index = $frequentIds->search($food->id);
                return $index === false ? 999999 : $index;
            })->values();
        }

        return $results;
    }

    public function getCategories(): array
    {
        return FoodItem::where('review_status', 'approved')
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getByCategory(string $category, ?int $userId = null): Collection
    {
        return FoodItem::where('category', $category)
            ->where('review_status', 'approved')
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    public function getPopularFoods(?int $userId = null, int $limit = 10): Collection
    {
        if (! $userId) {
            return FoodItem::where('review_status', 'approved')
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        $frequentIds = DB::table('meal_records')
            ->where('user_id', $userId)
            ->where('date', '>=', now()->subDays(30))
            ->select('food_id', DB::raw('count(*) as cnt'))
            ->groupBy('food_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('food_id');

        if ($frequentIds->isEmpty()) {
            return FoodItem::where('review_status', 'approved')
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return FoodItem::whereIn('id', $frequentIds)
            ->where('review_status', 'approved')
            ->get();
    }

    public function createCustomFood(int $userId, array $data): FoodItem
    {
        return FoodItem::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'aliases' => isset($data['aliases']) ? json_encode($data['aliases']) : null,
            'category' => $data['category'] ?? '其他',
            'calories_per_100g' => $data['calories_per_100g'],
            'protein_per_100g' => $data['protein_per_100g'] ?? 0,
            'carbs_per_100g' => $data['carbs_per_100g'] ?? 0,
            'fat_per_100g' => $data['fat_per_100g'] ?? 0,
            'serving_size' => $data['serving_size'] ?? 100,
            'serving_unit' => $data['serving_unit'] ?? 'g',
            'source' => 'user_custom',
            'review_status' => 'approved',
            'is_user_custom' => true,
            'version' => 1,
        ]);
    }

    public function toggleFavorite(int $userId, int $foodId): bool
    {
        $existing = FavoriteFood::where('user_id', $userId)
            ->where('food_id', $foodId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        // Check limit (50 favorites)
        $count = FavoriteFood::where('user_id', $userId)->count();
        if ($count >= 50) {
            return false; // At limit
        }

        FavoriteFood::create([
            'user_id' => $userId,
            'food_id' => $foodId,
            'used_at' => now(),
        ]);

        return true;
    }

    public function getFavorites(int $userId): Collection
    {
        return FavoriteFood::where('user_id', $userId)
            ->with('food')
            ->orderByDesc('used_at')
            ->limit(50)
            ->get()
            ->pluck('food');
    }

    public function isFavorite(int $userId, int $foodId): bool
    {
        return FavoriteFood::where('user_id', $userId)
            ->where('food_id', $foodId)
            ->exists();
    }

    public function getCustomFoods(int $userId): Collection
    {
        return FoodItem::where('user_id', $userId)
            ->where('is_user_custom', true)
            ->orderByDesc('updated_at')
            ->get();
    }

    public function updateCustomFood(int $userId, int $foodId, array $data): FoodItem
    {
        $food = FoodItem::where('id', $foodId)
            ->where('user_id', $userId)
            ->where('is_user_custom', true)
            ->firstOrFail();

        $food->update([
            'name' => $data['name'],
            'category' => $data['category'] ?? $food->category,
            'calories_per_100g' => $data['calories_per_100g'],
            'protein_per_100g' => $data['protein_per_100g'] ?? 0,
            'carbs_per_100g' => $data['carbs_per_100g'] ?? 0,
            'fat_per_100g' => $data['fat_per_100g'] ?? 0,
            'version' => $food->version + 1,
        ]);

        return $food->fresh();
    }

    public function deleteCustomFood(int $userId, int $foodId): bool
    {
        $food = FoodItem::where('id', $foodId)
            ->where('user_id', $userId)
            ->where('is_user_custom', true)
            ->firstOrFail();

        return $food->delete();
    }
}
