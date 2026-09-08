<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
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

    public function test_new_user_sees_onboarding(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('showOnboarding');
    }

    public function test_complete_onboarding(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('onboarding.complete'));
        $response->assertRedirect(route('dashboard'));

        $this->user->refresh();
        $this->assertTrue($this->user->preferences->onboarding_completed);
    }

    public function test_skip_onboarding(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('onboarding.skip'));
        $response->assertRedirect(route('dashboard'));

        $this->user->refresh();
        $this->assertTrue($this->user->preferences->onboarding_completed);
    }

    public function test_completed_user_no_onboarding(): void
    {
        UserPreference::create([
            'user_id' => $this->user->id,
            'onboarding_completed' => true,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('showOnboarding');
        $response->assertSee('false');
    }

    public function test_onboarding_steps_count(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        // Check that 4 steps are defined in the onboarding
        $response->assertSee('首页看板');
        $response->assertSee('饮食记录');
        $response->assertSee('运动记录');
        $response->assertSee('体重记录');
    }
}
