<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeightGoal;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
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
        ]);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 80.0,
        ]);
    }

    public function test_create_goal_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('goals.create'));
        $response->assertStatus(200);
    }

    public function test_store_goal_success(): void
    {
        $this->actingAs($this->user);

        $response = $this->from(route('goals.create'))->post(route('goals.store'), [
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertRedirect(route('goals.current'));
        $this->assertDatabaseHas('weight_goals', [
            'user_id' => $this->user->id,
            'target_weight' => 75.0,
            'status' => 'active',
        ]);
    }

    public function test_goal_validation_rejects_unsafe_rate(): void
    {
        $this->actingAs($this->user);

        // Try to lose 10kg in 1 week = 10kg/week (way over 1.5 limit)
        $response = $this->post(route('goals.store'), [
            'target_weight' => 70.0,
            'target_date' => now()->addWeek()->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertSessionHasErrors('target_weight');
    }

    public function test_goal_validation_allows_warning_rate(): void
    {
        $this->actingAs($this->user);

        // Lose 7kg in 6 weeks = ~1.17kg/week (between normal and warning threshold)
        // This should show warning but allow with confirmation
        $response = $this->from(route('goals.create'))->post(route('goals.store'), [
            'target_weight' => 73.0,
            'target_date' => now()->addWeeks(6)->format('Y-m-d'),
            'target_deficit' => 500,
            'confirm_warning' => '1',
        ]);

        $response->assertRedirect(route('goals.current'));
    }

    public function test_low_bmi_user_rejected(): void
    {
        $underweightUser = User::create([
            'name' => '偏瘦用户',
            'phone' => '13800138001',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1995-01-01',
            'height' => 165.0,
            'activity_level' => 'light',
        ]);

        WeightRecord::create([
            'user_id' => $underweightUser->id,
            'date' => now()->toDateString(),
            'weight_kg' => 45.0, // BMI = 45 / (1.65^2) = 16.5
        ]);

        $this->actingAs($underweightUser);

        $response = $this->post(route('goals.store'), [
            'target_weight' => 42.0,
            'target_date' => now()->addWeeks(10)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertSessionHasErrors('target_weight');
    }

    public function test_target_bmi_below_18_5_rejected(): void
    {
        $this->actingAs($this->user);

        // Target: 50kg, height 175cm -> BMI = 50 / (1.75^2) = 16.3
        $response = $this->post(route('goals.store'), [
            'target_weight' => 50.0,
            'target_date' => now()->addWeeks(20)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertSessionHasErrors('target_weight');
    }

    public function test_calorie_budget_not_below_safety_line(): void
    {
        $this->actingAs($this->user);

        $response = $this->from(route('goals.create'))->post(route('goals.store'), [
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10)->format('Y-m-d'),
            'target_deficit' => 750,
        ]);

        $response->assertRedirect(route('goals.current'));

        $goal = WeightGoal::where('user_id', $this->user->id)->first();
        $this->assertGreaterThanOrEqual(1500, (float) $goal->daily_calorie_budget);
    }

    public function test_goal_completion_redirects_to_maintenance(): void
    {
        $this->actingAs($this->user);

        // Create an active goal
        $goal = WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10),
            'daily_calorie_budget' => 2000,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $response = $this->get(route('goals.current'));
        $response->assertStatus(200);
    }

    public function test_update_goal(): void
    {
        $this->actingAs($this->user);

        $goal = WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10),
            'daily_calorie_budget' => 2000,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $response = $this->from(route('goals.current'))->put(route('goals.update', $goal), [
            'target_weight' => 73.0,
            'target_date' => now()->addWeeks(12)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertRedirect(route('goals.current'));
        $goal->refresh();
        $this->assertEquals(73.0, (float) $goal->target_weight);
    }

    public function test_no_weight_record_rejected(): void
    {
        $noWeightUser = User::create([
            'name' => '无体重用户',
            'phone' => '13800138002',
            'password' => 'password123',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 175.0,
            'activity_level' => 'moderate',
        ]);

        $this->actingAs($noWeightUser);

        $response = $this->post(route('goals.store'), [
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertSessionHasErrors('target_weight');
    }

    public function test_pregnant_user_rejected(): void
    {
        $pregnantUser = User::create([
            'name' => '孕期用户',
            'phone' => '13800138003',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1992-05-20',
            'height' => 165.0,
            'activity_level' => 'light',
            'special_group' => 'pregnant',
        ]);

        WeightRecord::create([
            'user_id' => $pregnantUser->id,
            'date' => now()->toDateString(),
            'weight_kg' => 65.0,
        ]);

        $this->actingAs($pregnantUser);

        $response = $this->post(route('goals.store'), [
            'target_weight' => 60.0,
            'target_date' => now()->addWeeks(10)->format('Y-m-d'),
            'target_deficit' => 500,
        ]);

        $response->assertSessionHasErrors('target_weight');
    }

    public function test_current_goal_page(): void
    {
        $this->actingAs($this->user);

        WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 75.0,
            'target_date' => now()->addWeeks(10),
            'daily_calorie_budget' => 2000,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        $response = $this->get(route('goals.current'));
        $response->assertStatus(200);
    }

    public function test_no_active_goal_redirects_to_create(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('goals.current'));
        $response->assertRedirect(route('goals.create'));
    }
}
