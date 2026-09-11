<?php

namespace Tests\Feature;

use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ExerciseType $exerciseType;

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

        $this->exerciseType = ExerciseType::create([
            'name' => '慢跑',
            'category' => '有氧',
            'met_value' => 7.0,
        ]);
    }

    public function test_create_exercise_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('exercises.create'));
        $response->assertStatus(200);
        $response->assertSee('记录运动');
    }

    public function test_store_exercise_record(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('exercises.store'), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('exercise_records', [
            'user_id' => $this->user->id,
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
        ]);
    }

    public function test_calories_calculation_net(): void
    {
        // Weight = 70kg, MET = 7.0, Duration = 30min = 0.5h
        // Net: (7.0 - 1) × 70 × 0.5 = 210 kcal
        $this->actingAs($this->user);

        $this->post(route('exercises.store'), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
        ]);

        $record = ExerciseRecord::where('user_id', $this->user->id)->first();
        $this->assertEquals(210.0, (float) $record->estimated_calories);
    }

    public function test_intensity_guess_from_met(): void
    {
        $this->actingAs($this->user);

        // MET 3.0 -> light
        $light = ExerciseType::create(['name' => '散步', 'category' => '有氧', 'met_value' => 2.5]);
        $this->post(route('exercises.store'), [
            'exercise_type_id' => $light->id,
            'duration_minutes' => 30,
        ]);
        $record = ExerciseRecord::where('exercise_type_id', $light->id)->first();
        $this->assertEquals('light', $record->intensity);

        // MET 7.0 -> heavy (>=6)
        $this->post(route('exercises.store'), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
        ]);
        $record = ExerciseRecord::where('exercise_type_id', $this->exerciseType->id)->first();
        $this->assertEquals('heavy', $record->intensity);
    }

    public function test_edit_exercise_record(): void
    {
        $record = ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 210.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('exercises.edit', $record));
        $response->assertStatus(200);
    }

    public function test_update_exercise_record(): void
    {
        $record = ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 210.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->put(route('exercises.update', $record), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 60,
            'intensity' => 'heavy',
        ]);

        $response->assertRedirect(route('dashboard'));
        $record->refresh();
        $this->assertEquals(60, $record->duration_minutes);
        $this->assertEquals('heavy', $record->intensity);
    }

    public function test_delete_exercise_record(): void
    {
        $record = ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 210.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('exercises.destroy', $record));
        $response->assertRedirect(route('exercises.index', ['date' => $record->date->toDateString()]));
        $this->assertSoftDeleted('exercise_records', ['id' => $record->id]);
    }

    public function test_duration_validation_range(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('exercises.store'), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 0,
        ]);
        $response->assertSessionHasErrors('duration_minutes');

        $response = $this->post(route('exercises.store'), [
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 700,
        ]);
        $response->assertSessionHasErrors('duration_minutes');
    }

    public function test_daily_exercise_summary(): void
    {
        $this->actingAs($this->user);

        ExerciseRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 210.0,
        ]);

        $response = $this->getJson(route('api.meals.dailySummary', now()->toDateString()));
        $response->assertStatus(200);
    }

    public function test_cannot_edit_other_users_record(): void
    {
        $otherUser = User::create([
            'name' => '其他用户',
            'phone' => '13900139000',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1995-01-01',
            'height' => 165.0,
            'activity_level' => 'light',
            'agreed_at' => now(),
        ]);

        $record = ExerciseRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'exercise_type_id' => $this->exerciseType->id,
            'duration_minutes' => 30,
            'intensity' => 'moderate',
            'estimated_calories' => 210.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('exercises.edit', $record));
        $response->assertStatus(403);
    }

    public function test_exercise_types_seeded(): void
    {
        $this->seed(\Database\Seeders\ExerciseTypeSeeder::class);

        $count = ExerciseType::count();
        $this->assertGreaterThanOrEqual(100, $count);
    }

    public function test_exercise_types_have_met_range(): void
    {
        $this->seed(\Database\Seeders\ExerciseTypeSeeder::class);

        $minMet = ExerciseType::min('met_value');
        $maxMet = ExerciseType::max('met_value');

        $this->assertGreaterThanOrEqual(1.5, $minMet);
        $this->assertLessThanOrEqual(20, $maxMet);
    }

    public function test_exercise_types_have_all_categories(): void
    {
        $this->seed(\Database\Seeders\ExerciseTypeSeeder::class);

        $categories = ExerciseType::pluck('category')->unique()->values();
        $this->assertContains('有氧', $categories);
        $this->assertContains('力量', $categories);
        $this->assertContains('柔韧', $categories);
        $this->assertContains('日常', $categories);
    }

    public function test_api_exercise_types(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.exerciseTypes'));
        $response->assertStatus(200);
        $response->assertJsonCount(ExerciseType::count());
    }

    public function test_api_exercise_types_filter_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.exerciseTypes') . '?category=有氧');
        $response->assertStatus(200);
        $data = $response->json();
        foreach ($data as $item) {
            $this->assertEquals('有氧', $item['category']);
        }
    }

    public function test_api_exercise_types_search(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.exerciseTypes') . '?search=慢跑');
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => '慢跑']);
    }
}
