<?php

namespace Tests\Feature;

use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateFilterTest extends TestCase
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

    public function test_meal_index_defaults_to_today(): void
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
            'serving_grams' => 100,
            'calculated_calories' => 133,
        ]);
        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDays(3)->toDateString(),
            'meal_type' => 'dinner',
            'food_id' => $food->id,
            'serving_grams' => 200,
            'calculated_calories' => 266,
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('meals.index'));

        $response->assertOk();
        $this->assertEquals(now()->toDateString(), $response->viewData('date'));
        $this->assertEquals(1, $response->viewData('records')->total());
        $this->assertEquals(now()->toDateString(), $response->viewData('records')->first()->date->toDateString());
    }

    public function test_meal_index_filters_by_selected_date(): void
    {
        $food = FoodItem::create([
            'name' => '米饭',
            'category' => '主食',
            'calories_per_100g' => 116,
            'protein_per_100g' => 2.6,
            'carbs_per_100g' => 25.9,
            'fat_per_100g' => 0.3,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        foreach ([0, 1, 5] as $offset) {
            MealRecord::create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($offset)->toDateString(),
                'meal_type' => 'lunch',
                'food_id' => $food->id,
                'serving_grams' => 100,
                'calculated_calories' => 116,
            ]);
        }

        $this->actingAs($this->user);
        $response = $this->get(route('meals.index', ['date' => now()->subDay()->toDateString()]));

        $response->assertOk();
        $this->assertEquals(now()->subDay()->toDateString(), $response->viewData('date'));
        $this->assertEquals(1, $response->viewData('records')->total());
    }

    public function test_exercise_index_filters_by_date(): void
    {
        $type = ExerciseType::create([
            'name' => '慢跑',
            'category' => '有氧',
            'met_value' => 7.0,
        ]);

        ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $type->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 250,
        ]);
        ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDays(7)->toDateString(),
            'exercise_type_id' => $type->id,
            'duration_minutes' => 40,
            'intensity' => 'moderate',
            'estimated_calories' => 300,
        ]);

        $this->actingAs($this->user);

        $today = $this->get(route('exercises.index'));
        $today->assertOk();
        $this->assertEquals(now()->toDateString(), $today->viewData('date'));
        $this->assertEquals(1, $today->viewData('records')->total());

        $past = $this->get(route('exercises.index', ['date' => now()->subDays(7)->toDateString()]));
        $past->assertOk();
        $this->assertEquals(1, $past->viewData('records')->total());
        $this->assertEquals(300, (int) $past->viewData('records')->first()->estimated_calories);
    }

    public function test_weight_index_filters_by_date(): void
    {
        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.0,
        ]);
        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDays(10)->toDateString(),
            'weight_kg' => 76.0,
        ]);

        $this->actingAs($this->user);

        $day = $this->get(route('weights.index'));
        $day->assertOk();
        $this->assertEquals(now()->toDateString(), $day->viewData('date'));
        $this->assertEquals(1, $day->viewData('records')->total());

        $past = $this->get(route('weights.index', ['date' => now()->subDays(10)->toDateString()]));
        $past->assertOk();
        $this->assertEquals(1, $past->viewData('records')->total());
        $this->assertEquals(76.0, (float) $past->viewData('records')->first()->weight_kg);
    }
}
