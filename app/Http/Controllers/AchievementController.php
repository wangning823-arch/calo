<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(
        private AchievementService $achievementService,
    ) {}

    public function index(Request $request): \Illuminate\View\View
    {
        $userId = $request->user()->id;
        $this->achievementService->checkAchievements($userId);
        $badges = $this->achievementService->getAchievementWall($userId);

        return view('achievements.index', compact('badges'));
    }
}
