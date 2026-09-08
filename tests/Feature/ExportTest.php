<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportTest extends TestCase
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

        MealRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_grams' => 200,
            'calculated_calories' => 400,
        ]);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.0,
        ]);
    }

    public function test_export_index_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('exports.index'));
        $response->assertStatus(200);
        $response->assertSee('数据导出');
        $response->assertSee('报表导出');
        $response->assertSee('完整数据导出');
    }

    public function test_export_meals_csv(): void
    {
        $this->actingAs($this->user);

        $dateRange = now()->subWeek()->format('Y-m-d').'..'.now()->format('Y-m-d');
        $response = $this->postJson(route('exports.report'), [
            'type' => 'meals',
            'date_range' => $dateRange,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_exercises_csv(): void
    {
        $this->actingAs($this->user);

        $dateRange = now()->subWeek()->format('Y-m-d').'..'.now()->format('Y-m-d');
        $response = $this->postJson(route('exports.report'), [
            'type' => 'exercises',
            'date_range' => $dateRange,
        ]);

        $response->assertStatus(200);
    }

    public function test_export_weights_csv(): void
    {
        $this->actingAs($this->user);

        $dateRange = now()->subWeek()->format('Y-m-d').'..'.now()->format('Y-m-d');
        $response = $this->postJson(route('exports.report'), [
            'type' => 'weights',
            'date_range' => $dateRange,
        ]);

        $response->assertStatus(200);
    }

    public function test_export_invalid_type(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('exports.report'), [
            'type' => 'invalid',
            'date_range' => '2024-01-01..2024-01-31',
        ]);

        $response->assertStatus(422);
    }

    public function test_full_data_export(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('exports.fullData'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'expires_in',
        ]);
    }

    public function test_download_with_valid_token(): void
    {
        $this->actingAs($this->user);

        // Create an export file first
        $exportResponse = $this->postJson(route('exports.fullData'));
        $token = $exportResponse->json('token');

        $response = $this->get(route('exports.download', $token));
        $response->assertStatus(200);
    }

    public function test_download_with_invalid_token(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('exports.download', 'invalid_token'));
        $response->assertStatus(404);
    }

    public function test_unauthenticated_cannot_export(): void
    {
        $response = $this->get(route('exports.index'));
        $response->assertRedirect();
    }

    public function test_pipl_notice_visible(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('exports.index'));
        $response->assertStatus(200);
        $response->assertSee('数据可携权');
        $response->assertSee('个人信息保护法');
    }
}
