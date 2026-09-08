<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HealthAlertController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Password change (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'changePassword']);
});

// Agreement (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/agreement', [AgreementController::class, 'show'])->name('agreement.show');
    Route::post('/agreement/accept', [AgreementController::class, 'accept'])->name('agreement.accept');
});

// Public pages
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/disclaimer', [PageController::class, 'disclaimer'])->name('pages.disclaimer');
Route::get('/icp', [PageController::class, 'icp'])->name('pages.icp');

// Profile (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Account cancellation (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/settings/cancellation', [AccountController::class, 'showCancellationForm'])->name('settings.cancellation');
    Route::post('/settings/cancellation', [AccountController::class, 'requestCancellation'])->name('settings.cancellation.request');
    Route::post('/settings/cancellation/cancel', [AccountController::class, 'cancelCancellation'])->name('settings.cancellation.cancel');
    Route::post('/settings/cancellation/execute', [AccountController::class, 'executeCancellation'])->name('settings.cancellation.execute');
});

// Onboarding (requires auth)
Route::middleware('auth')->group(function () {
    Route::post('/onboarding/complete', function (\Illuminate\Http\Request $r) {
        $r->user()->preferences()->updateOrCreate(
            ['user_id' => $r->user()->id],
            ['onboarding_completed' => true]
        );
        return redirect()->route('dashboard')->with('success', '引导已完成！');
    })->name('onboarding.complete');

    Route::post('/onboarding/skip', function (\Illuminate\Http\Request $r) {
        $r->user()->preferences()->updateOrCreate(
            ['user_id' => $r->user()->id],
            ['onboarding_completed' => true]
        );
        return redirect()->route('dashboard');
    })->name('onboarding.skip');
});

// Goals (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::get('/goals/current', [GoalController::class, 'current'])->name('goals.current');
    Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
});

// Foods (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
    Route::get('/foods/search', [FoodController::class, 'search'])->name('foods.search');
    Route::get('/foods/create', fn () => redirect()->route('dashboard'))->name('foods.create');
    Route::post('/foods', [FoodController::class, 'store'])->name('foods.store');
    Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show');
    Route::post('/foods/{food}/favorite', [FoodController::class, 'toggleFavorite'])->name('foods.favorite');
    Route::get('/foods/favorites/list', [FoodController::class, 'favorites'])->name('foods.favorites');
    Route::get('/api/foods/search', [FoodController::class, 'apiSearch'])->name('api.foods.search');
});

// Meals (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/meals/create', [MealController::class, 'create'])->name('meals.create');
    Route::post('/meals', [MealController::class, 'store'])->name('meals.store');
    Route::get('/meals/{meal}/edit', [MealController::class, 'edit'])->name('meals.edit');
    Route::put('/meals/{meal}', [MealController::class, 'update'])->name('meals.update');
    Route::delete('/meals/{meal}', [MealController::class, 'destroy'])->name('meals.destroy');
    Route::post('/meals/copy-yesterday/{mealType}', [MealController::class, 'copyYesterday'])->name('meals.copyYesterday');
    Route::get('/api/meals/daily-summary/{date}', [MealController::class, 'dailySummary'])->name('api.meals.dailySummary');
});

// Exercises (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/exercises/create', [ExerciseController::class, 'create'])->name('exercises.create');
    Route::post('/exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('/exercises/{record}/edit', [ExerciseController::class, 'edit'])->name('exercises.edit');
    Route::put('/exercises/{record}', [ExerciseController::class, 'update'])->name('exercises.update');
    Route::delete('/exercises/{record}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
    Route::get('/api/exercise-types', [ExerciseController::class, 'apiExerciseTypes'])->name('api.exerciseTypes');
});

// Weights (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/weights/create', [WeightController::class, 'create'])->name('weights.create');
    Route::post('/weights', [WeightController::class, 'store'])->name('weights.store');
    Route::get('/weights/{record}/edit', [WeightController::class, 'edit'])->name('weights.edit');
    Route::put('/weights/{record}', [WeightController::class, 'update'])->name('weights.update');
    Route::delete('/weights/{record}', [WeightController::class, 'destroy'])->name('weights.destroy');
    Route::get('/weights/trend', [WeightController::class, 'trend'])->name('weights.trend');
    Route::get('/api/weights/trend', [WeightController::class, 'trendApi'])->name('api.weights.trend');
});

// Predictions (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::get('/api/predictions', [PredictionController::class, 'api'])->name('api.predictions');
});

// Health alerts (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/api/health-alerts/check', [HealthAlertController::class, 'check'])->name('api.healthAlerts.check');
    Route::post('/api/health-alerts/confirm', [HealthAlertController::class, 'confirm'])->name('api.healthAlerts.confirm');
});

// Reports (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/reports/weekly/{date}', [ReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('/reports/monthly/{date}', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/history', [ReportController::class, 'history'])->name('reports.history');
});

// Exports (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');
    Route::post('/exports/report', [ExportController::class, 'report'])->name('exports.report');
    Route::post('/exports/full-data', [ExportController::class, 'fullData'])->name('exports.fullData');
    Route::get('/exports/download/{token}', [ExportController::class, 'download'])->name('exports.download');
});

// Notifications (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.updatePreferences');
});

// Achievements (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
});

// Content (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/content/recipes', [ContentController::class, 'recipes'])->name('content.recipes');
    Route::get('/content/recipes/{id}', [ContentController::class, 'recipe'])->name('content.recipe');
    Route::post('/content/recipes/{id}/import', [ContentController::class, 'importRecipe'])->name('content.importRecipe');
    Route::get('/content/training-plans', [ContentController::class, 'trainingPlans'])->name('content.trainingPlans');
    Route::get('/content/training-plans/{id}', [ContentController::class, 'planDetail'])->name('content.planDetail');
    Route::post('/content/training-plans/{id}/adopt', [ContentController::class, 'adoptPlan'])->name('content.adoptPlan');
    Route::post('/content/training-plans/{adoptionId}/check-in', [ContentController::class, 'checkIn'])->name('content.checkIn');
});

// Dashboard (requires auth + agreement)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserAgreement::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/today', [DashboardController::class, 'todayApi'])->name('api.dashboard.today');
});
