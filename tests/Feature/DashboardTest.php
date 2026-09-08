<?php

namespace Tests\Feature;

use App\Models\ExerciseRecord;
use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightGoal;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => '测试用户',
            'phone' => '13800138000',
            'password' => 'password123',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 175.0,
            'activity_level' => 'moderate',
            'agreed_at' => now(),
        ]);
    }

    public function test_dashboard_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Calo');
    }

    public function test_dashboard_with_goal(): void
    {
        WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 70.0,
            'target_date' => now()->addMonths(3),
            'daily_calorie_budget' => 1800,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_dashboard_calorie_calculation(): void
    {
        WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 70.0,
            'target_date' => now()->addMonths(3),
            'daily_calorie_budget' => 1800,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $food = FoodItem::create([
            'name' => '鸡胸肉',
            'category' => '肉类',
            'calories_per_100g' => 133,
            'protein_per_100g' => 31.0,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 200,
            'calculated_calories' => 266.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson(route('api.dashboard.today'));
        $response->assertStatus(200);
        $response->assertJson([
            'budget' => 1800.0,
            'intake_calories' => 266.0,
            'remaining' => 1534.0,
        ]);
    }

    public function test_dashboard_status_colors(): void
    {
        WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 70.0,
            'target_date' => now()->addMonths(3),
            'daily_calorie_budget' => 1800,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 500,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 50,
            'fat_per_100g' => 30,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        // Eat more than 125% of budget -> 'over'
        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 500,
            'calculated_calories' => 2500,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson(route('api.dashboard.today'));
        $response->assertJson(['status' => 'over']);
    }

    public function test_streak_days(): void
    {
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 100,
            'protein_per_100g' => 5,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 3,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        // Create records for today and yesterday
        for ($i = 0; $i < 3; $i++) {
            MealRecord::create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($i)->toDateString(),
                'meal_type' => 'lunch',
                'food_id' => $food->id,
                'serving_grams' => 100,
                'calculated_calories' => 100,
            ]);
        }

        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('连续3天');
    }

    public function test_no_goal_shows_no_budget(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.dashboard.today'));
        $response->assertStatus(200);
        $response->assertJson([
            'budget' => null,
            'remaining' => null,
        ]);
    }

    public function test_recent_weight(): void
    {
        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.5,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('75.5');
    }

    public function test_nutrition_progress(): void
    {
        $food = FoodItem::create([
            'name' => '鸡胸肉',
            'category' => '肉类',
            'calories_per_100g' => 133,
            'protein_per_100g' => 31.0,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 200,
            'calculated_calories' => 266.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson(route('api.dashboard.today'));
        $response->assertStatus(200);
        $response->assertJson([
            'intake_protein' => 62.0,
            'intake_carbs' => 0,
            'intake_fat' => 7.2,
        ]);
    }
}
