<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\Recipe;
use App\Models\TrainingPlan;
use App\Models\TrainingPlanAdoption;
use App\Models\User;
use App\Models\WeightGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
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
    }

    public function test_recipes_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('content.recipes'));
        $response->assertStatus(200);
        $response->assertSee('食谱库');
    }

    public function test_recipes_page_empty(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('content.recipes'));
        $response->assertStatus(200);
        $response->assertSee('暂无食谱');
    }

    public function test_recipes_page_with_data(): void
    {
        Recipe::create([
            'title' => '测试食谱',
            'meal_type' => 'lunch',
            'total_calories' => 500,
            'protein' => 30,
            'carbs' => 60,
            'fat' => 15,
            'ingredients' => json_encode([['name' => '鸡胸肉', 'amount' => '200g']]),
            'steps' => json_encode([['step' => '第一步']]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('content.recipes'));
        $response->assertStatus(200);
        $response->assertSee('测试食谱');
        $response->assertSee('500');
    }

    public function test_recipe_detail(): void
    {
        $recipe = Recipe::create([
            'title' => '详细食谱',
            'meal_type' => 'dinner',
            'total_calories' => 600,
            'protein' => 40,
            'carbs' => 70,
            'fat' => 20,
            'ingredients' => json_encode([['name' => '三文鱼', 'amount' => '150g']]),
            'steps' => json_encode([['step' => '准备食材']]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('content.recipe', $recipe->id));
        $response->assertStatus(200);
        $response->assertSee('详细食谱');
        $response->assertSee('食材清单');
        $response->assertSee('制作步骤');
    }

    public function test_recipe_detail_404(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('content.recipe', 999));
        $response->assertStatus(404);
    }

    public function test_import_recipe(): void
    {
        $recipe = Recipe::create([
            'title' => '导入食谱',
            'meal_type' => 'lunch',
            'total_calories' => 400,
            'protein' => 25,
            'carbs' => 50,
            'fat' => 10,
            'ingredients' => json_encode([['name' => '测试食材', 'amount' => '100g']]),
            'steps' => json_encode([]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('content.importRecipe', $recipe->id), [
            'meal_type' => 'lunch',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('meal_records', [
            'user_id' => $this->user->id,
            'meal_type' => 'lunch',
        ]);
    }

    public function test_training_plans_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('content.trainingPlans'));
        $response->assertStatus(200);
        $response->assertSee('训练计划');
    }

    public function test_training_plans_with_data(): void
    {
        TrainingPlan::create([
            'title' => '减脂计划',
            'goal' => 'lose',
            'difficulty' => 'beginner',
            'duration_weeks' => 4,
            'exercises' => json_encode([['name' => '深蹲', 'sets' => '3', 'reps' => '15']]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('content.trainingPlans'));
        $response->assertStatus(200);
        $response->assertSee('减脂计划');
        $response->assertSee('减脂');
        $response->assertSee('新手');
    }

    public function test_plan_detail(): void
    {
        $plan = TrainingPlan::create([
            'title' => '塑形计划',
            'goal' => 'shape',
            'difficulty' => 'advanced',
            'duration_weeks' => 8,
            'exercises' => json_encode([['name' => '硬拉', 'sets' => '4', 'reps' => '10', 'rest' => '90秒']]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('content.planDetail', $plan->id));
        $response->assertStatus(200);
        $response->assertSee('塑形计划');
        $response->assertSee('训练动作');
    }

    public function test_adopt_plan(): void
    {
        $plan = TrainingPlan::create([
            'title' => '采用计划',
            'goal' => 'lose',
            'difficulty' => 'beginner',
            'duration_weeks' => 4,
            'exercises' => json_encode([]),
            'status' => 'published',
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('content.adoptPlan', $plan->id));
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('training_plan_adoptions', [
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_check_in(): void
    {
        $plan = TrainingPlan::create([
            'title' => '打卡计划',
            'goal' => 'lose',
            'difficulty' => 'beginner',
            'duration_weeks' => 4,
            'exercises' => json_encode([]),
            'status' => 'published',
        ]);

        $adoption = TrainingPlanAdoption::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'status' => 'in_progress',
            'check_in_days' => json_encode([]),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('content.checkIn', $adoption->id));
        $response->assertRedirect();

        $adoption->refresh();
        $checkInDays = json_decode($adoption->check_in_days, true);
        $this->assertContains(now()->toDateString(), $checkInDays);
    }

    public function test_disclaimer_visible(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('content.recipes'));
        $response->assertStatus(200);
        $response->assertSee('非医疗建议');
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $response = $this->get(route('content.recipes'));
        $response->assertRedirect();
    }
}
