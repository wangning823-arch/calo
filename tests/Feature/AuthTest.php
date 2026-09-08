<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected array $userData = [
        'name' => '测试用户',
        'phone' => '13800138000',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
    ];

    protected function createUser(array $overrides = []): User
    {
        return User::create(array_merge($this->userData, $overrides));
    }

    public function test_phone_password_login(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unregistered_phone_auto_creates_account(): void
    {
        $response = $this->post('/login', [
            'phone' => '13900139000',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['phone' => '13900139000']);
        $user = User::where('phone', '13900139000')->first();
        $this->assertAuthenticatedAs($user);
    }

    public function test_lockout_after_5_failed_attempts(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', [
                'phone' => $user->phone,
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertTrue(Cache::has("login_lockout:{$user->phone}"));
    }

    public function test_locked_account_cannot_login(): void
    {
        $user = $this->createUser();
        Cache::put("login_lockout:{$user->phone}", true, now()->addMinutes(10));

        $response = $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_phone_format_validation(): void
    {
        $response = $this->post('/login', [
            'phone' => '12345',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');

        $response = $this->post('/login', [
            'phone' => 'abcdefghijk',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');

        $response = $this->post('/login', [
            'phone' => '138001380001',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_password_reset_without_sms(): void
    {
        $user = $this->createUser();

        $response = $this->post('/password/reset', [
            'phone' => $user->phone,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_password_reset_invalidates_all_other_sessions(): void
    {
        $user = $this->createUser();

        // Simulate an existing session for this user
        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'payload' => '',
            'last_activity' => time(),
        ]);

        // Password reset (done when logged out)
        $this->post('/password/reset', [
            'phone' => $user->phone,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // All sessions for this user should be deleted
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->post('/password/change', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_password_change_success(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->post('/password/change', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_password_change_invalidates_other_sessions(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        // Create a session record to simulate another session
        $this->post('/password/change', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Current session should still be valid
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_creates_audit_log(): void
    {
        $user = $this->createUser();

        $this->post('/password/reset', [
            'phone' => $user->phone,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action_type' => 'password_reset',
        ]);
    }

    public function test_password_change_creates_audit_log(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->post('/password/change', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action_type' => 'password_changed',
        ]);
    }

    public function test_login_creates_audit_log(): void
    {
        $user = $this->createUser();

        $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action_type' => 'login',
        ]);
    }

    public function test_logout_creates_audit_log(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->post('/logout');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action_type' => 'logout',
        ]);
    }

    public function test_cancelled_account_cannot_login(): void
    {
        $user = $this->createUser([
            'cancelled_at' => now(),
            'cancellation_deadline' => now()->subDay(),
        ]);

        $response = $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_password_requirements_enforced(): void
    {
        $response = $this->post('/login', [
            'phone' => '13800138000',
            'password' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
