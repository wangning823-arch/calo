<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodTest extends TestCase
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
        ]);

        // Create test food items
        FoodItem::create([
            'name' => '鸡胸肉',
            'aliases' => json_encode(['鸡脯肉', '鸡柳']),
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

        FoodItem::create([
            'name' => '白米饭',
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

        FoodItem::create([
            'name' => '苹果',
            'category' => '水果',
            'calories_per_100g' => 52,
            'protein_per_100g' => 0.3,
            'carbs_per_100g' => 13.8,
            'fat_per_100g' => 0.2,
            'source' => 'crawled',
            'review_status' => 'approved',
            'is_user_custom' => false,
            'version' => 1,
        ]);
    }

    public function test_search_food(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('foods.search', ['q' => '鸡胸']));
        $response->assertStatus(200);
    }

    public function test_search_response_time(): void
    {
        $this->actingAs($this->user);

        $start = microtime(true);
        $this->get(route('foods.search', ['q' => '鸡']));
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(0.5, $elapsed);
    }

    public function test_no_results_guides_custom_food(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('foods.search', ['q' => '不存在的食物']));
        $response->assertStatus(200);
        $response->assertSee('创建自定义食物');
    }

    public function test_create_custom_food(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('foods.store'), [
            'name' => '我的秘制沙拉',
            'category' => '其他',
            'calories_per_100g' => 85,
            'protein_per_100g' => 3.5,
            'carbs_per_100g' => 8.2,
            'fat_per_100g' => 4.8,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('food_items', [
            'name' => '我的秘制沙拉',
            'is_user_custom' => true,
        ]);
    }

    public function test_favorites_limit(): void
    {
        $this->actingAs($this->user);

        // Create 50 favorites
        for ($i = 0; $i < 50; $i++) {
            $food = FoodItem::create([
                'name' => "测试食物{$i}",
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

            $this->post(route('foods.favorite', $food));
        }

        // 51st should fail
        $food51 = FoodItem::create([
            'name' => '测试食物51',
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

        $response = $this->post(route('foods.favorite', $food51));
        $response->assertSessionHas('success', '已取消收藏。');
    }

    public function test_toggle_favorite(): void
    {
        $this->actingAs($this->user);

        $food = FoodItem::first();

        // Add favorite
        $response = $this->post(route('foods.favorite', $food));
        $response->assertSessionHas('success', '已添加到收藏。');

        // Remove favorite
        $response = $this->post(route('foods.favorite', $food));
        $response->assertSessionHas('success', '已取消收藏。');
    }

    public function test_favorites_list(): void
    {
        $this->actingAs($this->user);

        $food = FoodItem::first();
        $this->post(route('foods.favorite', $food));

        $response = $this->get(route('foods.favorites'));
        $response->assertStatus(200);
    }

    public function test_category_browse(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('foods.index'));
        $response->assertStatus(200);
    }

    public function test_food_detail(): void
    {
        $this->actingAs($this->user);

        $food = FoodItem::first();
        $response = $this->get(route('foods.show', $food));
        $response->assertStatus(200);
        $response->assertSee($food->name);
    }

    public function test_api_search(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.foods.search', ['q' => '鸡']));
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
