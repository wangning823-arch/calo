<?php

namespace Tests\Feature;

use App\Models\MealRecord;
use App\Models\FoodItem;
use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthAlertTest extends TestCase
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

    public function test_check_alert_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.healthAlerts.check'));
        $response->assertStatus(200);
        $response->assertJson(['has_alert' => false]);
    }

    public function test_daily_low_intake_alert(): void
    {
        $this->actingAs($this->user);

        // Create a very low calorie meal (well below 1500kcal safety line)
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 50,
            'protein_per_100g' => 2,
            'carbs_per_100g' => 8,
            'fat_per_100g' => 1,
            'source' => 'official',
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
            'calculated_calories' => 50, // Very low
        ]);

        $response = $this->getJson(route('api.healthAlerts.check'));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['has_alert']);
        $this->assertEquals('daily_low_intake', $data['alert']['type']);
    }

    public function test_no_alert_when_intake_adequate(): void
    {
        $this->actingAs($this->user);

        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '主食',
            'calories_per_100g' => 300,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 50,
            'fat_per_100g' => 5,
            'source' => 'official',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 500,
            'calculated_calories' => 1500, // Adequate
        ]);

        $response = $this->getJson(route('api.healthAlerts.check'));
        $response->assertStatus(200);
        $response->assertJson(['has_alert' => false]);
    }

    public function test_alert_level_escalation(): void
    {
        $this->actingAs($this->user);

        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 50,
            'protein_per_100g' => 2,
            'carbs_per_100g' => 8,
            'fat_per_100g' => 1,
            'source' => 'official',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        // Create 5 days of low intake
        for ($i = 4; $i >= 0; $i--) {
            MealRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'meal_type' => 'lunch',
                'food_id' => $food->id,
                'serving_grams' => 100,
                'calculated_calories' => 50,
            ]);
        }

        $response = $this->getJson(route('api.healthAlerts.check'));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['has_alert']);
        $this->assertEquals('persistent', $data['level']);
        $this->assertEquals(5, $data['consecutive_days']);
    }

    public function test_confirm_alert(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('api.healthAlerts.confirm'), [
            'choice' => 'acknowledge',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action_type' => 'health_alert_confirmed',
        ]);
    }

    public function test_confirm_alert_invalid_choice(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('api.healthAlerts.confirm'), [
            'choice' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_female_lower_safety_line(): void
    {
        $femaleUser = User::create([
            'name' => '女性用户',
            'phone' => '13900139000',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1995-06-15',
            'height' => 165.0,
            'activity_level' => 'light',
            'agreed_at' => now(),
        ]);

        $this->actingAs($femaleUser);

        // 1300 kcal is above female line (1200) but below male line (1500)
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 100,
            'protein_per_100g' => 5,
            'carbs_per_100g' => 15,
            'fat_per_100g' => 2,
            'source' => 'official',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        MealRecord::create([
            'user_id' => $femaleUser->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 1300,
            'calculated_calories' => 1300,
        ]);

        $response = $this->getJson(route('api.healthAlerts.check'));
        $response->assertStatus(200);
        $response->assertJson(['has_alert' => false]); // 1300 >= 1200 female line
    }
}
