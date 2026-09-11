<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FoodItem $food;

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
        ]);

        $this->food = FoodItem::create([
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
    }

    public function test_record_meal_success(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 200,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('meal_records', [
            'user_id' => $this->user->id,
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 200,
            'calculated_calories' => 266.0,
        ]);
    }

    public function test_calorie_calculation(): void
    {
        $this->actingAs($this->user);

        $this->post(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 150,
        ]);

        // 133 * 150 / 100 = 199.5
        $this->assertDatabaseHas('meal_records', [
            'calculated_calories' => 199.5,
        ]);
    }

    public function test_daily_summary(): void
    {
        $this->actingAs($this->user);

        // Record breakfast and lunch
        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'breakfast',
            'food_id' => $this->food->id,
            'serving_grams' => 100,
            'calculated_calories' => 133,
        ]);

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $this->food->id,
            'serving_grams' => 200,
            'calculated_calories' => 266,
        ]);

        $response = $this->getJson(route('api.meals.dailySummary', now()->toDateString()));
        $response->assertStatus(200);
        $response->assertJson([
            'total_calories' => 399.0,
            'record_count' => 2,
        ]);
    }

    public function test_edit_record_within_30_days(): void
    {
        $this->actingAs($this->user);

        $record = MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $this->food->id,
            'serving_grams' => 100,
            'calculated_calories' => 133,
        ]);

        $response = $this->put(route('meals.update', $record), [
            'food_id' => $this->food->id,
            'meal_type' => 'dinner',
            'serving_grams' => 250,
        ]);

        $response->assertRedirect(route('dashboard'));
        $record->refresh();
        $this->assertEquals('dinner', $record->meal_type);
        $this->assertEquals(250, (float) $record->serving_grams);
    }

    public function test_delete_record(): void
    {
        $this->actingAs($this->user);

        $record = MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $this->food->id,
            'serving_grams' => 100,
            'calculated_calories' => 133,
        ]);

        $response = $this->delete(route('meals.destroy', $record));
        $response->assertRedirect(route('meals.index', ['date' => $record->date->toDateString()]));
        $this->assertSoftDeleted('meal_records', ['id' => $record->id]);
    }

    public function test_copy_yesterday_meal(): void
    {
        $this->actingAs($this->user);

        // Create yesterday's record
        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $this->food->id,
            'serving_grams' => 200,
            'calculated_calories' => 266,
        ]);

        $response = $this->post(route('meals.copyYesterday', 'lunch'));
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('meal_records', [
            'user_id' => $this->user->id,
            'meal_type' => 'lunch',
            'notes' => '复制自昨日',
        ]);
    }

    public function test_update_meal_with_common_serving_grams(): void
    {
        $this->actingAs($this->user);

        $record = MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $this->food->id,
            'serving_grams' => 100,
            'calculated_calories' => 133,
        ]);

        foreach ([50, 100, 150, 200, 250] as $grams) {
            $response = $this->put(route('meals.update', $record), [
                'food_id' => $this->food->id,
                'meal_type' => 'lunch',
                'serving_grams' => $grams,
            ]);

            $response->assertSessionHasNoErrors();
            $record->refresh();
            $this->assertEquals($grams, (float) $record->serving_grams);
        }
    }

    public function test_serving_grams_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 0,
        ]);

        $response->assertSessionHasErrors('serving_grams');

        $response = $this->post(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 6000,
        ]);

        $response->assertSessionHasErrors('serving_grams');
    }

    public function test_meal_type_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'invalid',
            'serving_grams' => 100,
        ]);

        $response->assertSessionHasErrors('meal_type');
    }

    public function test_create_meal_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('meals.create'));
        $response->assertStatus(200);
        $response->assertSee('记录饮食');
    }

    public function test_json_response(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('meals.store'), [
            'food_id' => $this->food->id,
            'meal_type' => 'lunch',
            'serving_grams' => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
