<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightGoal extends Model
{
    protected $fillable = [
        'user_id',
        'mode',
        'start_weight',
        'target_weight',
        'target_date',
        'daily_calorie_budget',
        'target_deficit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_weight' => 'decimal:2',
            'target_weight' => 'decimal:2',
            'target_date' => 'date',
            'daily_calorie_budget' => 'decimal:1',
            'target_deficit' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
