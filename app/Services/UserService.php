<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    private const ACTIVITY_MULTIPLIERS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'heavy' => 1.725,
    ];

    public function calculateBMR(User $user): float
    {
        $weight = (float) $user->height; // placeholder, will use weight from weight_records
        $height = (float) $user->height;
        $age = $user->getAge() ?? 25;

        // Use latest weight record if available, otherwise use a default
        $latestWeight = $user->weightRecords()->latest('date')->first();
        $weightKg = $latestWeight ? (float) $latestWeight->weight_kg : 70.0;

        if ($user->gender === 'male') {
            return (10 * $weightKg) + (6.25 * $height) - (5 * $age) + 5;
        }

        return (10 * $weightKg) + (6.25 * $height) - (5 * $age) - 161;
    }

    public function calculateTDEE(User $user): float
    {
        $bmr = $this->calculateBMR($user);
        $multiplier = self::ACTIVITY_MULTIPLIERS[$user->activity_level] ?? 1.2;

        return $bmr * $multiplier;
    }

    public function calculateAge(User $user): ?int
    {
        return $user->date_of_birth?->age;
    }

    public function validateBMI(float $weightKg, float $heightCm): array
    {
        $heightM = $heightCm / 100;
        $bmi = $weightKg / ($heightM * $heightM);

        $status = 'normal';
        $message = null;

        if ($bmi < 18.5) {
            $status = 'underweight';
            $message = '您的BMI偏低，建议增重后再设定减重目标。';
        } elseif ($bmi >= 18.5 && $bmi < 24) {
            $status = 'normal';
        } elseif ($bmi >= 24 && $bmi < 28) {
            $status = 'overweight';
            $message = '您的BMI偏高，建议适当控制饮食。';
        } else {
            $status = 'obese';
            $message = '您的BMI较高，建议咨询医生后制定减重计划。';
        }

        return [
            'bmi' => round($bmi, 1),
            'status' => $status,
            'message' => $message,
        ];
    }

    public function calculateDailyCalorieBudget(User $user): float
    {
        $tdee = $this->calculateTDEE($user);

        // Default deficit of 500 kcal for weight loss
        $deficit = 500;

        // Safety lines
        $minCalories = $user->gender === 'female' ? 1200 : 1500;

        $budget = $tdee - $deficit;

        return max($budget, $minCalories);
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'height' => $data['height'],
            'activity_level' => $data['activity_level'],
            'unit_preference' => $data['unit_preference'] ?? $user->unit_preference ?? 'jin',
        ]);

        // Recalculate and update weight goal if active
        $activeGoal = $user->activeWeightGoal;
        if ($activeGoal) {
            $dailyBudget = $this->calculateDailyCalorieBudget($user);
            $activeGoal->update(['daily_calorie_budget' => $dailyBudget]);
        }

        return $user->fresh();
    }
}
