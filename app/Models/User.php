<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'is_admin',
        'password',
        'gender',
        'date_of_birth',
        'height',
        'activity_level',
        'special_group',
        'unit_preference',
        'agreed_at',
        'cancelled_at',
        'cancellation_deadline',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_token',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'date_of_birth' => 'date',
            'height' => 'decimal:1',
            'agreed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancellation_deadline' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    public function weightGoal(): HasOne
    {
        return $this->hasOne(WeightGoal::class)->latest();
    }

    public function weightGoals(): HasMany
    {
        return $this->hasMany(WeightGoal::class);
    }

    public function activeWeightGoal(): HasOne
    {
        return $this->hasOne(WeightGoal::class)->where('status', 'active')->latest();
    }

    public function mealRecords(): HasMany
    {
        return $this->hasMany(MealRecord::class);
    }

    public function exerciseRecords(): HasMany
    {
        return $this->hasMany(ExerciseRecord::class);
    }

    public function weightRecords(): HasMany
    {
        return $this->hasMany(WeightRecord::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function favoriteFoods(): HasMany
    {
        return $this->hasMany(FavoriteFood::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(AchievementReminder::class)->where('type', 'achievement');
    }

    public function trainingPlanAdoptions(): HasMany
    {
        return $this->hasMany(TrainingPlanAdoption::class);
    }

    public function foodCorrections(): HasMany
    {
        return $this->hasMany(FoodCorrection::class, 'user_id');
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isCancellationPending(): bool
    {
        return $this->cancelled_at !== null
            && $this->cancellation_deadline !== null
            && $this->cancellation_deadline->isFuture();
    }

    public function isMinor(): bool
    {
        return $this->date_of_birth && $this->date_of_birth->age < 18;
    }

    public function getAge(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
