<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::create([
            'name' => '管理员',
            'phone' => '13900139000',
            'password' => 'admin123',
            'gender' => 'male',
            'date_of_birth' => '1985-01-01',
        ]);
    }

    public function test_admin_panel_requires_auth(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect();
    }

    public function test_food_item_resource_accessible(): void
    {
        // Verify the resource class exists and can be loaded
        $this->assertTrue(class_exists(\App\Filament\Resources\FoodItemResource::class));
    }

    public function test_food_item_crud(): void
    {
        $food = FoodItem::create([
            'name' => '测试管理食物',
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

        $this->assertDatabaseHas('food_items', ['name' => '测试管理食物']);

        // Test approval
        $food->update(['review_status' => 'approved']);
        $this->assertDatabaseHas('food_items', ['name' => '测试管理食物', 'review_status' => 'approved']);
    }

    public function test_food_item_resource_exists(): void
    {
        $this->assertTrue(class_exists(\App\Filament\Resources\FoodItemResource::class));
        $this->assertTrue(class_exists(\App\Filament\Resources\FeedbackResource::class));
        $this->assertTrue(class_exists(\App\Filament\Resources\FoodCorrectionResource::class));
    }
}
