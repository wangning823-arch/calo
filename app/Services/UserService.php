<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    // Sedentary multiplier (desk job, minimal daily movement)
    // Actual exercise is tracked separately via exercise_records
    private const BASE_ACTIVITY_MULTIPLIER = 1.2;

    public function calculateBMR(User $user): float
    {
        $height = (float) $user->height;
        $age = $user->getAge() ?? 25;

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

        return $bmr * self::BASE_ACTIVITY_MULTIPLIER;
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

    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'height' => $data['height'],
            'activity_level' => $data['activity_level'] ?? 'sedentary',
            'unit_preference' => $data['unit_preference'] ?? $user->unit_preference ?? 'jin',
        ]);

        // Recalculate and update weight goal if active
        $activeGoal = $user->activeWeightGoal;
        if ($activeGoal) {
            $goalService = app(GoalService::class);
            $budgetResult = $goalService->calculateDailyBudget($user, $activeGoal->target_deficit);
            $activeGoal->update([
                'daily_calorie_budget' => $budgetResult['budget'],
                'target_deficit' => $budgetResult['actual_deficit'],
            ]);
        }

        return $user->fresh();
    }
}
