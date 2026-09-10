<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FoodCorrection;
use App\Models\FoodItem;
use App\Models\Feedback;
use App\Models\NotificationLog;
use App\Models\Recipe;
use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => '管理员',
            'phone' => '13900139000',
            'is_admin' => true,
        ]);
    }

    public function test_admin_requires_auth(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_admin_logout_redirects_to_main_login(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/logout')
            ->assertRedirect('/login');
    }

    public function test_admin_dashboard_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_all_admin_resource_pages_load(): void
    {
        $food = FoodItem::create([
            'name' => '测试食物',
            'category' => '其他',
            'calories_per_100g' => 100,
            'protein_per_100g' => 5,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 3,
            'source' => 'crawled',
            'review_status' => 'pending',
            'is_user_custom' => false,
            'version' => 1,
        ]);

        $recipe = Recipe::create([
            'title' => '测试食谱',
            'meal_type' => 'lunch',
            'total_calories' => 300,
            'protein' => 20,
            'carbs' => 30,
            'fat' => 10,
            'ingredients' => [['name' => '米饭', 'amount' => '1碗']],
            'steps' => [['step' => '煮熟']],
            'status' => 'draft',
        ]);

        $plan = TrainingPlan::create([
            'title' => '测试计划',
            'goal' => 'lose',
            'difficulty' => 'beginner',
            'duration_weeks' => 4,
            'exercises' => [['name' => '深蹲', 'sets' => 3, 'reps' => 12, 'rest' => '60s']],
            'status' => 'draft',
        ]);

        $feedback = Feedback::create([
            'user_id' => $this->admin->id,
            'type' => 'bug',
            'content' => '测试反馈',
            'status' => 'open',
        ]);

        $correction = FoodCorrection::create([
            'user_id' => $this->admin->id,
            'food_id' => $food->id,
            'correction_content' => '热量有误',
            'review_status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'action_type' => 'login',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        NotificationLog::create([
            'user_id' => $this->admin->id,
            'type' => 'system',
            'title' => '系统通知',
            'content' => '测试通知内容',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $pages = [
            '/admin',
            '/admin/users',
            '/admin/food-items',
            '/admin/recipes',
            '/admin/training-plans',
            '/admin/feedback',
            '/admin/food-corrections',
            '/admin/audit-logs',
            '/admin/notification-logs',
            '/admin/import-food-data',
            "/admin/food-items/{$food->id}/edit",
            "/admin/recipes/{$recipe->id}/edit",
            "/admin/training-plans/{$plan->id}/edit",
            "/admin/feedback/{$feedback->id}/edit",
            "/admin/food-corrections/{$correction->id}/edit",
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->admin)
                ->get($page)
                ->assertOk("Failed on page: {$page}");
        }
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'phone' => '13900139001',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_food_item_create_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/food-items/create')
            ->assertOk();
    }

    public function test_recipe_create_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/recipes/create')
            ->assertOk();
    }

    public function test_training_plan_create_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/training-plans/create')
            ->assertOk();
    }
}
