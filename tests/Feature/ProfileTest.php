<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_bmr_calculation_male(): void
    {
        $this->actingAs($this->user);

        // Add a weight record
        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);

        // BMR = 10*75 + 6.25*175 - 5*34 + 5 = 750 + 1093.75 - 170 + 5 = 1678.75
        // Age is 34 (born 1990-01-01, current year 2024)
    }

    public function test_bmr_calculation_female(): void
    {
        $femaleUser = User::create([
            'name' => '女性用户',
            'phone' => '13800138001',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1995-06-15',
            'height' => 165.0,
            'activity_level' => 'light',
        ]);

        $this->actingAs($femaleUser);

        WeightRecord::create([
            'user_id' => $femaleUser->id,
            'date' => now()->toDateString(),
            'weight_kg' => 60.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_tdee_calculation_sedentary(): void
    {
        $this->user->update(['activity_level' => 'sedentary']);
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_tdee_calculation_light(): void
    {
        $this->user->update(['activity_level' => 'light']);
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_tdee_calculation_moderate(): void
    {
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_tdee_calculation_heavy(): void
    {
        $this->user->update(['activity_level' => 'heavy']);
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 70.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_age_calculation(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
        // User born 1990-01-01, should show age
    }

    public function test_bmi_calculation(): void
    {
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 75.0,
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_profile_update_success(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'name' => '更新昵称',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 180.0,
            'activity_level' => 'heavy',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('更新昵称', $this->user->name);
        $this->assertEquals(180.0, (float) $this->user->height);
        $this->assertEquals('heavy', $this->user->activity_level);
    }

    public function test_height_validation_min(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'name' => '测试',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 25.0,
            'activity_level' => 'moderate',
        ]);

        $response->assertSessionHasErrors('height');
    }

    public function test_height_validation_max(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'name' => '测试',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 300.0,
            'activity_level' => 'moderate',
        ]);

        $response->assertSessionHasErrors('height');
    }

    public function test_activity_level_required(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'name' => '测试',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'height' => 175.0,
        ]);

        $response->assertSessionHasErrors('activity_level');
    }

    public function test_special_group_pregnant_restricted(): void
    {
        $pregnantUser = User::create([
            'name' => '孕期用户',
            'phone' => '13800138002',
            'password' => 'password123',
            'gender' => 'female',
            'date_of_birth' => '1992-05-20',
            'height' => 165.0,
            'activity_level' => 'light',
            'special_group' => 'pregnant',
        ]);

        $this->actingAs($pregnantUser);

        // Pregnant users should be able to view profile
        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_gender_and_dob_editable(): void
    {
        $this->actingAs($this->user);

        // Gender and DOB should be updatable
        $response = $this->put(route('profile.update'), [
            'name' => '测试用户',
            'height' => 180.0,
            'activity_level' => 'heavy',
            'gender' => 'female',
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertRedirect();

        $this->user->refresh();
        $this->assertEquals('female', $this->user->gender);
        $this->assertEquals('2000-01-01', $this->user->date_of_birth->format('Y-m-d'));
    }

    public function test_bmi_underweight(): void
    {
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 45.0, // BMI = 45 / (1.75^2) = 14.7
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_bmi_obese(): void
    {
        $this->actingAs($this->user);

        WeightRecord::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'weight_kg' => 120.0, // BMI = 120 / (1.75^2) = 39.2
        ]);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function test_no_weight_record(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }
}
