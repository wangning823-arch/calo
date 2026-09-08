<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountService
{
    private const CANCELLATION_COOLDOWN_DAYS = 7;

    public function requestCancellation(User $user): void
    {
        $user->update([
            'cancelled_at' => now(),
            'cancellation_deadline' => now()->addDays(self::CANCELLATION_COOLDOWN_DAYS),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'cancellation_requested',
            'action_details' => [
                'deadline' => $user->cancellation_deadline->toIso8601String(),
            ],
            'ip_address' => request()->ip(),
        ]);
    }

    public function cancelCancellation(User $user): void
    {
        $user->update([
            'cancelled_at' => null,
            'cancellation_deadline' => null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'cancellation_revoked',
            'action_details' => [],
            'ip_address' => request()->ip(),
        ]);
    }

    public function executeCancellation(User $user): void
    {
        $userId = $user->id;

        DB::beginTransaction();

        try {
            // Delete all personal data
            DB::table('meal_records')->where('user_id', $userId)->delete();
            DB::table('exercise_records')->where('user_id', $userId)->delete();
            DB::table('weight_records')->where('user_id', $userId)->delete();
            DB::table('weight_goals')->where('user_id', $userId)->delete();
            DB::table('favorite_foods')->where('user_id', $userId)->delete();
            DB::table('achievement_reminders')->where('user_id', $userId)->delete();
            DB::table('notification_logs')->where('user_id', $userId)->delete();
            DB::table('feedbacks')->where('user_id', $userId)->delete();
            DB::table('food_corrections')->where('user_id', $userId)->delete();
            DB::table('training_plan_adoptions')->where('user_id', $userId)->delete();
            DB::table('user_preferences')->where('user_id', $userId)->delete();

            // Delete food items created by this user
            DB::table('food_items')->where('is_user_custom', true)
                ->where('id', DB::table('meal_records')->where('user_id', $userId)->select('food_id'))
                ->delete();

            // Delete user
            $user->forceDelete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function validatePassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
