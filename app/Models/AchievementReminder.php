<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementReminder extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'badge_type',
        'reminder_config',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'reminder_config' => 'array',
            'earned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
