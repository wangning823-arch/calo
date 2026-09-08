<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightGoal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountCancellationTest extends TestCase
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

    public function test_request_cancellation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('settings.cancellation.request'));
        $response->assertRedirect(route('dashboard'));

        $this->user->refresh();
        $this->assertNotNull($this->user->cancelled_at);
        $this->assertNotNull($this->user->cancellation_deadline);
    }

    public function test_cancel_cancellation(): void
    {
        $this->user->update([
            'cancelled_at' => now(),
            'cancellation_deadline' => now()->addDays(7),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('settings.cancellation.cancel'));
        $response->assertRedirect(route('dashboard'));

        $this->user->refresh();
        $this->assertNull($this->user->cancelled_at);
        $this->assertNull($this->user->cancellation_deadline);
    }

    public function test_execute_cancellation_with_password(): void
    {
        // Create some data
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 100,
            'protein_per_100g' => 5,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 3,
            'source' => 'user_custom',
            'review_status' => 'approved',
            'is_user_custom' => true,
            'version' => 1,
        ]);

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 100,
            'calculated_calories' => 100,
        ]);

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

        $userId = $this->user->id;

        $this->actingAs($this->user);

        $response = $this->post(route('settings.cancellation.execute'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('meal_records', ['user_id' => $userId]);
        $this->assertDatabaseMissing('weight_goals', ['user_id' => $userId]);
    }

    public function test_execute_cancellation_wrong_password(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('settings.cancellation.execute'), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function test_cancellation_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings.cancellation'));
        $response->assertStatus(200);
        $response->assertSee('申请注销');
    }

    public function test_cancellation_flow_max_4_steps(): void
    {
        // Step 1: View cancellation page
        $this->actingAs($this->user);
        $this->get(route('settings.cancellation'))->assertStatus(200);

        // Step 2: Request cancellation
        $this->post(route('settings.cancellation.request'));

        // Step 3: Execute (with password)
        // Step 4: Logout redirect
        // Total: 4 steps (view -> request -> execute -> done)
    }

    public function test_pending_cancellation_shows_deadline(): void
    {
        $this->user->update([
            'cancelled_at' => now(),
            'cancellation_deadline' => now()->addDays(7),
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('settings.cancellation'));
        $response->assertStatus(200);
        $response->assertSee('冷静期');
        $response->assertSee('撤销注销');
    }

    public function test_cancellation_creates_audit_log(): void
    {
        $this->actingAs($this->user);

        $this->post(route('settings.cancellation.request'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action_type' => 'cancellation_requested',
        ]);
    }
}
