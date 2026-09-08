<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightGoal;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

        $this->seedMealData();
    }

    protected function seedMealData(): void
    {
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '主食',
            'calories_per_100g' => 200,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 30,
            'fat_per_100g' => 5,
            'source' => 'official',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 70.0,
            'target_date' => Carbon::now()->addMonths(3),
            'daily_calorie_budget' => 2000,
            'target_deficit' => 500,
            'status' => 'active',
        ]);

        for ($i = 6; $i >= 0; $i--) {
            MealRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'meal_type' => 'lunch',
                'food_id' => $food->id,
                'serving_grams' => 200,
                'calculated_calories' => 400,
            ]);
        }

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->subDays(7)->toDateString(),
            'weight_kg' => 80.0,
        ]);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->toDateString(),
            'weight_kg' => 79.5,
        ]);
    }

    public function test_weekly_report_success(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('周报');
        $response->assertSee('日均摄入');
    }

    public function test_weekly_report_calorie_data(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('2000');
    }

    public function test_weekly_report_weight_change(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('体重变化');
        $response->assertSee('-0.5');
    }

    public function test_weekly_report_nutrient_structure(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('蛋白质');
        $response->assertSee('碳水');
        $response->assertSee('脂肪');
    }

    public function test_weekly_report_completion_rate(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('记录完成率');
    }

    public function test_monthly_report_success(): void
    {
        $this->actingAs($this->user);

        $month = Carbon::now()->format('Y-m');
        $response = $this->get(route('reports.monthly', $month));
        $response->assertStatus(200);
        $response->assertSee('月报');
    }

    public function test_monthly_report_has_weeks(): void
    {
        $this->actingAs($this->user);

        $month = Carbon::now()->format('Y-m');
        $response = $this->get(route('reports.monthly', $month));
        $response->assertStatus(200);
        $response->assertSee('每周趋势');
    }

    public function test_report_history(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.history'));
        $response->assertStatus(200);
        $response->assertSee('报告历史');
        $response->assertSee('周报');
        $response->assertSee('月报');
    }

    public function test_report_history_empty(): void
    {
        $emptyUser = User::create([
            'name' => '空用户',
            'phone' => '13800138001',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1995-06-15',
            'height' => 165.0,
            'activity_level' => 'light',
        ]);

        $this->actingAs($emptyUser);

        $response = $this->get(route('reports.history'));
        $response->assertStatus(200);
        $response->assertSee('暂无报告');
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertRedirect();
    }

    public function test_weekly_report_healthy_loss_days(): void
    {
        $this->actingAs($this->user);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('健康减重天数');
    }

    public function test_weekly_report_no_data(): void
    {
        $emptyUser = User::create([
            'name' => '新用户',
            'phone' => '13800138002',
            'password' => 'password123',
            'gender' => 'male',
            'date_of_birth' => '1992-03-10',
            'height' => 180.0,
            'activity_level' => 'heavy',
        ]);

        $this->actingAs($emptyUser);

        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $response = $this->get(route('reports.weekly', $weekStart));
        $response->assertStatus(200);
        $response->assertSee('周报');
    }
}
