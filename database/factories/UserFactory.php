<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('1##########'),
            'password' => static::$password ??= Hash::make('password'),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'height' => fake()->randomFloat(1, 150, 200),
            'activity_level' => fake()->randomElement(['sedentary', 'light', 'moderate', 'heavy']),
            'special_group' => 'none',
            'unit_preference' => 'kg',
            'remember_token' => Str::random(10),
        ];
    }
}
