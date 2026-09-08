<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseType extends Model
{
    protected $fillable = [
        'name',
        'category',
        'met_value',
    ];

    protected function casts(): array
    {
        return [
            'met_value' => 'decimal:1',
        ];
    }
}
