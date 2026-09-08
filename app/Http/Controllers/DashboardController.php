<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $today = $this->dashboardService->getTodayData($user->id);
        $streak = $this->dashboardService->getStreakDays($user->id);
        $recentWeight = $this->dashboardService->getRecentWeight($user->id);
        $showOnboarding = ! $user->preferences?->onboarding_completed;

        return view('dashboard.index', compact('user', 'today', 'streak', 'recentWeight', 'showOnboarding'));
    }

    public function todayApi(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getTodayData($request->user()->id);

        return response()->json($data);
    }
}
