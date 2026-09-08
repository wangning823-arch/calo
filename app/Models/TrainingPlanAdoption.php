<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlanAdoption extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'status',
        'check_in_days',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'check_in_days' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class, 'plan_id');
    }
}
