<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'goal',
        'difficulty',
        'duration_weeks',
        'exercises',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exercises' => 'array',
        ];
    }
}
