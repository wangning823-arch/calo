<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeightGoal;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionTest extends TestCase
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

    private function createGoal(): WeightGoal
    {
        return WeightGoal::create([
            'user_id' => $this->user->id,
            'mode' => 'lose',
            'start_weight' => 80.0,
            'target_weight' => 70.0,
            'target_date' => Carbon::now()->addMonths(3),
            'daily_calorie_budget' => 1800,
            'target_deficit' => 500,
            'status' => 'active',
        ]);
    }

    public function test_prediction_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('predictions.index'));
        $response->assertStatus(200);
    }

    public function test_prediction_with_insufficient_data(): void
    {
        $this->createGoal();
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $data = $response->json('prediction');
        $this->assertFalse($data['predictable']);
        $this->assertArrayHasKey('days_needed', $data);
    }

    public function test_prediction_with_sufficient_data(): void
    {
        $this->createGoal();
        $this->actingAs($this->user);

        // Create 14 days of weight records showing steady loss
        for ($i = 13; $i >= 0; $i--) {
            WeightRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'weight_kg' => 78.0 - (13 - $i) * 0.1, // Losing ~0.7kg/week
            ]);
        }

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $data = $response->json('prediction');
        $this->assertTrue($data['predictable']);
        $this->assertArrayHasKey('predicted_date', $data);
        $this->assertArrayHasKey('days_to_goal', $data);
    }

    public function test_plateau_detection(): void
    {
        $this->createGoal();
        $this->actingAs($this->user);

        // Create 21 days of nearly constant weight (plateau)
        for ($i = 20; $i >= 0; $i--) {
            WeightRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'weight_kg' => 75.0 + sin($i) * 0.05, // Very small fluctuation
            ]);
        }

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $data = $response->json('plateau');
        $this->assertNotNull($data);
        $this->assertTrue($data['is_plateau']);
        $this->assertArrayHasKey('suggestions', $data);
    }

    public function test_no_plateau_with_normal_loss(): void
    {
        $this->createGoal();
        $this->actingAs($this->user);

        // Create 21 days of steady weight loss
        for ($i = 20; $i >= 0; $i--) {
            WeightRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'weight_kg' => 78.0 - (20 - $i) * 0.15, // Normal loss
            ]);
        }

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $this->assertNull($response->json('plateau'));
    }

    public function test_goal_progress(): void
    {
        $this->createGoal();
        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.0, // Lost 5kg of 10kg goal = 50%
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $progress = $response->json('progress');
        $this->assertEquals(50.0, $progress['progress']);
    }

    public function test_no_goal_returns_null(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.predictions'));
        $response->assertStatus(200);
        $this->assertNull($response->json('prediction'));
        $this->assertNull($response->json('progress'));
    }
}
