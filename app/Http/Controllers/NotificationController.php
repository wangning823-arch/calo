<?php

namespace App\Http\Controllers;

use App\Services\ReminderService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private ReminderService $reminderService,
    ) {}

    public function index(Request $request): \Illuminate\View\View
    {
        $userId = $request->user()->id;
        $notifications = $this->reminderService->getNotificationHistory($userId);
        $unreadCount = $this->reminderService->getUnreadCount($userId);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $this->reminderService->markAsRead($request->user()->id, $id);

        return back()->with('success', '已标记为已读。');
    }

    public function readAll(Request $request): \Illuminate\Http\RedirectResponse
    {
        $count = $this->reminderService->markAllAsRead($request->user()->id);

        return back()->with('success', "已标记{$count}条通知为已读。");
    }

    public function preferences(Request $request): \Illuminate\View\View
    {
        $preferences = $this->reminderService->getPreferences($request->user()->id);

        return view('notifications.preferences', compact('preferences'));
    }

    public function updatePreferences(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'notification_breakfast' => ['boolean'],
            'notification_lunch' => ['boolean'],
            'notification_dinner' => ['boolean'],
            'notification_weigh_in' => ['boolean'],
            'breakfast_time' => ['date_format:H:i'],
            'lunch_time' => ['date_format:H:i'],
            'dinner_time' => ['date_format:H:i'],
            'weigh_in_time' => ['date_format:H:i'],
        ]);

        $this->reminderService->updatePreferences($request->user()->id, $validated);

        return redirect()->route('notifications.preferences')
            ->with('success', '通知偏好已更新。');
    }
}
