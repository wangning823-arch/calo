<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'meal_type',
        'total_calories',
        'protein',
        'carbs',
        'fat',
        'ingredients',
        'steps',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_calories' => 'decimal:1',
            'protein' => 'decimal:1',
            'carbs' => 'decimal:1',
            'fat' => 'decimal:1',
            'ingredients' => 'array',
            'steps' => 'array',
        ];
    }
}
