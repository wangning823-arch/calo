<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use App\Models\UserPreference;
use Carbon\Carbon;

class ReminderService
{
    public function getNotificationHistory(int $userId, int $limit = 50): array
    {
        return NotificationLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getUnreadCount(int $userId): int
    {
        return NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(int $userId, int $notificationId): bool
    {
        $notification = NotificationLog::where('user_id', $userId)
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return false;
        }

        $notification->update(['read_at' => now()]);

        return true;
    }

    public function markAllAsRead(int $userId): int
    {
        return NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getPreferences(int $userId): array
    {
        $prefs = UserPreference::where('user_id', $userId)->first();

        if (! $prefs) {
            $prefs = UserPreference::create(['user_id' => $userId]);
        }

        return [
            'notification_breakfast' => $prefs->notification_breakfast,
            'notification_lunch' => $prefs->notification_lunch,
            'notification_dinner' => $prefs->notification_dinner,
            'notification_weigh_in' => $prefs->notification_weigh_in,
            'notification_health_alert' => $prefs->notification_health_alert,
            'notification_achievement' => $prefs->notification_achievement,
            'breakfast_time' => $prefs->breakfast_time,
            'lunch_time' => $prefs->lunch_time,
            'dinner_time' => $prefs->dinner_time,
            'weigh_in_time' => $prefs->weigh_in_time,
        ];
    }

    public function updatePreferences(int $userId, array $data): bool
    {
        $prefs = UserPreference::where('user_id', $userId)->first();

        if (! $prefs) {
            $prefs = UserPreference::create(['user_id' => $userId]);
        }

        $prefs->update($data);

        return true;
    }

    public function sendNotification(int $userId, string $type, string $title, string $content): NotificationLog
    {
        return NotificationLog::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'send_status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function getRemindersDue(): array
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $users = User::whereNull('cancelled_at')
            ->with('preferences')
            ->get();

        $due = [];

        foreach ($users as $user) {
            $prefs = $user->preferences;
            if (! $prefs) {
                continue;
            }

            if ($prefs->notification_breakfast && $prefs->breakfast_time === $currentTime) {
                $due[] = ['user_id' => $user->id, 'type' => 'breakfast', 'title' => '早餐提醒', 'content' => '记得记录今天的早餐哦！'];
            }
            if ($prefs->notification_lunch && $prefs->lunch_time === $currentTime) {
                $due[] = ['user_id' => $user->id, 'type' => 'lunch', 'title' => '午餐提醒', 'content' => '记得记录今天的午餐哦！'];
            }
            if ($prefs->notification_dinner && $prefs->dinner_time === $currentTime) {
                $due[] = ['user_id' => $user->id, 'type' => 'dinner', 'title' => '晚餐提醒', 'content' => '记得记录今天的晚餐哦！'];
            }
            if ($prefs->notification_weigh_in && $prefs->weigh_in_time === $currentTime) {
                $due[] = ['user_id' => $user->id, 'type' => 'weigh_in', 'title' => '称重提醒', 'content' => '记得记录今天的体重！'];
            }
        }

        return $due;
    }
}
