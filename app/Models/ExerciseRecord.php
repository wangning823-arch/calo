<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExerciseRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'date',
        'recorded_at',
        'exercise_type_id',
        'duration_minutes',
        'intensity',
        'estimated_calories',
        'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recorded_at' => 'datetime',
            'estimated_calories' => 'decimal:1',
            'distance_km' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class);
    }
}
