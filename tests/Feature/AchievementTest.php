<?php

namespace Tests\Feature;

use App\Models\AchievementReminder;
use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Models\FoodItem;
use App\Models\MealRecord;
use App\Models\User;
use App\Models\WeightGoal;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
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

    public function test_achievements_index_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('achievements.index'));
        $response->assertStatus(200);
        $response->assertSee('成就墙');
        $response->assertSee('初来乍到');
    }

    public function test_first_register_badge(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('achievements.index'));
        $response->assertStatus(200);

        // First register badge should be earned after checking
        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'first_register',
        ]);
    }

    public function test_streak_3_badge(): void
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

        // Create 3 days of records
        for ($i = 2; $i >= 0; $i--) {
            MealRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'meal_type' => 'lunch',
                'food_id' => $food->id,
                'serving_grams' => 100,
                'calculated_calories' => 200,
            ]);
        }

        $this->actingAs($this->user);
        $this->get(route('achievements.index'));

        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'streak_3',
        ]);
    }

    public function test_first_goal_badge(): void
    {
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

        $this->actingAs($this->user);
        $this->get(route('achievements.index'));

        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'first_goal',
        ]);
    }

    public function test_goal_5_percent_badge(): void
    {
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

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 79.5, // Lost 0.5kg out of 10kg = 5%
        ]);

        $this->actingAs($this->user);
        $this->get(route('achievements.index'));

        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'goal_5',
        ]);
    }

    public function test_first_exercise_badge(): void
    {
        $exerciseType = ExerciseType::create([
            'name' => '慢跑',
            'category' => '有氧',
            'met_value' => 7.0,
        ]);

        ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 250,
        ]);

        $this->actingAs($this->user);
        $this->get(route('achievements.index'));

        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'first_exercise',
        ]);
    }

    public function test_exercise_100_minutes_badge(): void
    {
        $exerciseType = ExerciseType::create([
            'name' => '慢跑',
            'category' => '有氧',
            'met_value' => 7.0,
        ]);

        // Create records totaling 100+ minutes
        for ($i = 0; $i < 5; $i++) {
            ExerciseRecord::create([
                'user_id' => $this->user->id,
                'date' => Carbon::now()->subDays($i)->toDateString(),
                'exercise_type_id' => $exerciseType->id,
                'duration_minutes' => 25,
                'intensity' => 'moderate',
                'estimated_calories' => 200,
            ]);
        }

        $this->actingAs($this->user);
        $this->get(route('achievements.index'));

        $this->assertDatabaseHas('achievement_reminders', [
            'user_id' => $this->user->id,
            'badge_type' => 'exercise_100',
        ]);
    }

    public function test_badges_not_earned_shown(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('achievements.index'));
        $response->assertStatus(200);
        $response->assertSee('未获得');
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $response = $this->get(route('achievements.index'));
        $response->assertRedirect();
    }

    public function test_all_badge_types_present(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('achievements.index'));
        $response->assertStatus(200);

        // Check all badge names are present
        $response->assertSee('初来乍到');
        $response->assertSee('三日坚持');
        $response->assertSee('一周达人');
        $response->assertSee('月度冠军');
        $response->assertSee('目标设定');
        $response->assertSee('小有成效');
        $response->assertSee('半程英雄');
        $response->assertSee('目标达人');
        $response->assertSee('运动新手');
        $response->assertSee('运动达人');
    }
}
