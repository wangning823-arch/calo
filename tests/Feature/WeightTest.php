<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightTest extends TestCase
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
            'unit_preference' => 'kg',
            'agreed_at' => now(),
        ]);
    }

    public function test_create_weight_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('weights.create'));
        $response->assertStatus(200);
        $response->assertSee('记录体重');
    }

    public function test_store_weight_record_kg(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('weights.store'), [
            'weight' => 72.5,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('weight_records', [
            'user_id' => $this->user->id,
            'weight_kg' => 72.50,
        ]);
    }

    public function test_weight_validation_range(): void
    {
        $this->actingAs($this->user);

        // Too low
        $response = $this->post(route('weights.store'), [
            'weight' => 5,
            'date' => now()->toDateString(),
        ]);
        $response->assertSessionHasErrors('weight');

        // Too high
        $response = $this->post(route('weights.store'), [
            'weight' => 180,
            'date' => now()->toDateString(),
        ]);
        $response->assertSessionHasErrors('weight');
    }

    public function test_same_day_multiple_records_updates_last(): void
    {
        $this->actingAs($this->user);

        $response1 = $this->post(route('weights.store'), [
            'weight' => 70,
            'date' => now()->toDateString(),
        ]);
        $response1->assertRedirect();

        $response2 = $this->post(route('weights.store'), [
            'weight' => 71,
            'date' => now()->toDateString(),
        ]);
        $response2->assertRedirect();

        $count = WeightRecord::where('user_id', $this->user->id)->count();
        $this->assertEquals(1, $count);

        $record = WeightRecord::where('user_id', $this->user->id)->first();
        $this->assertEquals(71.0, (float) $record->weight_kg);
    }

    public function test_edit_weight_record(): void
    {
        $record = WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDay()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('weights.edit', $record));
        $response->assertStatus(200);
    }

    public function test_update_weight_record(): void
    {
        $record = WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDay()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->put(route('weights.update', $record), [
            'weight' => 69.0,
        ]);

        $response->assertRedirect(route('weights.trend'));
        $record->refresh();
        $this->assertEquals(69.0, (float) $record->weight_kg);
    }

    public function test_delete_weight_record(): void
    {
        $record = WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->subDay()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('weights.destroy', $record));
        $response->assertRedirect(route('weights.index', ['date' => $record->date->toDateString()]));
        $this->assertSoftDeleted('weight_records', ['id' => $record->id]);
    }

    public function test_7day_moving_average(): void
    {
        $this->actingAs($this->user);

        for ($i = 6; $i >= 0; $i--) {
            WeightRecord::create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($i)->toDateString(),
                'weight_kg' => 70.0 + $i,
            ]);
        }

        $response = $this->get(route('weights.trend'));
        $response->assertStatus(200);
    }

    public function test_trend_api(): void
    {
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $response = $this->getJson(route('api.weights.trend') . '?period=month');
        $response->assertStatus(200);
        $response->assertJsonStructure(['records', 'moving_average', 'period', 'latest']);
    }

    public function test_trend_chart_data(): void
    {
        $this->actingAs($this->user);

        for ($i = 5; $i >= 0; $i--) {
            WeightRecord::create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($i)->toDateString(),
                'weight_kg' => 70.0,
            ]);
        }

        $response = $this->getJson(route('api.weights.trend', ['period' => 'week']));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(6, $data['records']);
        $this->assertCount(6, $data['moving_average']);
    }

    public function test_optional_measurements(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('weights.store'), [
            'weight' => 70,
            'date' => now()->toDateString(),
            'body_fat_percentage' => 22.5,
            'waist_cm' => 82.0,
            'hip_cm' => 95.0,
        ]);

        $response->assertRedirect(route('dashboard'));
        $record = WeightRecord::where('user_id', $this->user->id)->first();
        $this->assertEquals(22.5, (float) $record->body_fat_percentage);
        $this->assertEquals(82.0, (float) $record->waist_cm);
        $this->assertEquals(95.0, (float) $record->hip_cm);
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

        $record = WeightRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'weight_kg' => 60.0,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('weights.edit', $record));
        $response->assertStatus(403);
    }
}
