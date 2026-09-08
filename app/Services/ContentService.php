<?php

namespace App\Services;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\Recipe;
use App\Models\TrainingPlan;
use App\Models\TrainingPlanAdoption;
use App\Models\WeightGoal;
use Carbon\Carbon;

class ContentService
{
    public function getRecipes(int $userId, ?float $remainingCalories = null): array
    {
        $recipes = Recipe::where('status', 'published')->get()->toArray();

        if ($remainingCalories !== null && $remainingCalories > 0) {
            $maxCalories = $remainingCalories * 0.4;
            usort($recipes, fn ($a, $b) => abs($a['total_calories'] - $maxCalories) <=> abs($b['total_calories'] - $maxCalories));
        }

        return $recipes;
    }

    public function getRecipe(int $id): ?array
    {
        $recipe = Recipe::find($id);

        return $recipe ? $recipe->toArray() : null;
    }

    public function importRecipeToMeal(int $userId, int $recipeId, string $mealType): bool
    {
        $recipe = Recipe::find($recipeId);
        if (! $recipe) {
            return false;
        }

        $ingredients = json_decode($recipe->ingredients, true) ?? [];

        foreach ($ingredients as $ingredient) {
            $food = FoodItem::whereRaw('LOWER(name) = ?', [strtolower($ingredient['name'] ?? '')])->first();

            if (! $food) {
                $food = FoodItem::create([
                    'name' => $ingredient['name'] ?? '未知食材',
                    'category' => '其他',
                    'calories_per_100g' => 100,
                    'protein_per_100g' => 5,
                    'carbs_per_100g' => 15,
                    'fat_per_100g' => 3,
                    'source' => 'official',
                    'review_status' => 'approved',
                    'is_user_custom' => false,
                    'version' => 1,
                ]);
            }

            MealRecord::create([
                'user_id' => $userId,
                'date' => now()->toDateString(),
                'meal_type' => $mealType,
                'food_id' => $food->id,
                'serving_grams' => 100,
                'calculated_calories' => $recipe->total_calories / max(1, count($ingredients)),
                'notes' => "从食谱「{$recipe->title}」导入",
            ]);
        }

        return true;
    }

    public function getTrainingPlans(int $userId): array
    {
        return TrainingPlan::where('status', 'published')
            ->get()
            ->toArray();
    }

    public function getTrainingPlan(int $id): ?array
    {
        $plan = TrainingPlan::find($id);

        return $plan ? $plan->toArray() : null;
    }

    public function adoptPlan(int $userId, int $planId): ?TrainingPlanAdoption
    {
        $plan = TrainingPlan::find($planId);
        if (! $plan) {
            return null;
        }

        // Check if already adopted
        $existing = TrainingPlanAdoption::where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return $existing;
        }

        return TrainingPlanAdoption::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'start_date' => now()->toDateString(),
            'status' => 'in_progress',
            'check_in_days' => json_encode([]),
        ]);
    }

    public function checkIn(int $userId, int $adoptionId): bool
    {
        $adoption = TrainingPlanAdoption::where('user_id', $userId)
            ->where('id', $adoptionId)
            ->first();

        if (! $adoption) {
            return false;
        }

        $checkInDays = json_decode($adoption->check_in_days, true) ?? [];
        $today = now()->toDateString();

        if (in_array($today, $checkInDays)) {
            return true; // Already checked in today
        }

        $checkInDays[] = $today;
        $adoption->update(['check_in_days' => json_encode($checkInDays)]);

        return true;
    }
}
