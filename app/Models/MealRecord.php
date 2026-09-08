<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'date',
        'recorded_at',
        'timezone',
        'meal_type',
        'food_id',
        'food_version',
        'serving_grams',
        'calculated_calories',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recorded_at' => 'datetime',
            'serving_grams' => 'decimal:1',
            'calculated_calories' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_id');
    }
}
