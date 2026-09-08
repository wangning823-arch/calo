<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderTest extends TestCase
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

    public function test_notification_index_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('通知中心');
    }

    public function test_notification_index_shows_empty(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('暂无通知');
    }

    public function test_notification_index_shows_notifications(): void
    {
        NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'reminder',
            'title' => '测试通知',
            'content' => '这是一条测试通知',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('测试通知');
        $response->assertSee('这是一条测试通知');
    }

    public function test_mark_notification_as_read(): void
    {
        $notification = NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'reminder',
            'title' => '测试通知',
            'content' => '内容',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('notifications.read', $notification->id));
        $response->assertRedirect();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'reminder',
            'title' => '通知1',
            'content' => '内容1',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'alert',
            'title' => '通知2',
            'content' => '内容2',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('notifications.readAll'));
        $response->assertRedirect();

        $this->assertEquals(0, NotificationLog::where('user_id', $this->user->id)->whereNull('read_at')->count());
    }

    public function test_preferences_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('notifications.preferences'));
        $response->assertStatus(200);
        $response->assertSee('通知偏好');
        $response->assertSee('饮食提醒');
        $response->assertSee('称重提醒');
    }

    public function test_update_preferences(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('notifications.updatePreferences'), [
            'notification_breakfast' => '1',
            'notification_lunch' => '0',
            'notification_dinner' => '1',
            'notification_weigh_in' => '1',
            'breakfast_time' => '07:30',
            'lunch_time' => '12:00',
            'dinner_time' => '18:30',
            'weigh_in_time' => '21:00',
        ]);

        $response->assertRedirect(route('notifications.preferences'));
        $response->assertSessionHas('success');

        $prefs = UserPreference::where('user_id', $this->user->id)->first();
        $this->assertTrue($prefs->notification_breakfast);
        $this->assertFalse($prefs->notification_lunch);
        $this->assertTrue($prefs->notification_dinner);
        $this->assertEquals('07:30', $prefs->breakfast_time);
    }

    public function test_health_alert_cannot_be_disabled(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('notifications.preferences'));
        $response->assertStatus(200);
        $response->assertSee('健康预警通知不可关闭');
    }

    public function test_unread_count_shown(): void
    {
        NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'reminder',
            'title' => '未读通知',
            'content' => '内容',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('1');
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $response = $this->get(route('notifications.index'));
        $response->assertRedirect();
    }

    public function test_notification_types_shown(): void
    {
        NotificationLog::create([
            'user_id' => $this->user->id,
            'type' => 'achievement',
            'title' => '成就获得',
            'content' => '恭喜获得徽章',
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('achievement');
    }
}
