<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_breakfast',
        'notification_lunch',
        'notification_dinner',
        'notification_weigh_in',
        'notification_health_alert',
        'notification_achievement',
        'breakfast_time',
        'lunch_time',
        'dinner_time',
        'weigh_in_time',
        'theme',
        'onboarding_completed',
        'exercise_refill_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notification_breakfast' => 'boolean',
            'notification_lunch' => 'boolean',
            'notification_dinner' => 'boolean',
            'notification_weigh_in' => 'boolean',
            'notification_health_alert' => 'boolean',
            'notification_achievement' => 'boolean',
            'onboarding_completed' => 'boolean',
            'exercise_refill_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
