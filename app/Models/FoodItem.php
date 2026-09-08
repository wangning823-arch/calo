<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'aliases',
        'category',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'serving_size',
        'serving_unit',
        'source',
        'review_status',
        'is_user_custom',
        'version',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'aliases' => 'array',
            'calories_per_100g' => 'decimal:1',
            'protein_per_100g' => 'decimal:1',
            'carbs_per_100g' => 'decimal:1',
            'fat_per_100g' => 'decimal:1',
            'serving_size' => 'decimal:1',
            'is_user_custom' => 'boolean',
            'version' => 'integer',
        ];
    }
}
