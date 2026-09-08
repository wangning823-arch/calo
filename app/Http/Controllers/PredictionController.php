<?php

namespace App\Http\Controllers;

use App\Services\PredictionService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        private PredictionService $predictionService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $prediction = $this->predictionService->predictGoalAchievement($user);
        $plateau = $this->predictionService->detectPlateau($user);
        $progress = $this->predictionService->getGoalProgress($user);

        return view('predictions.index', compact('user', 'prediction', 'plateau', 'progress'));
    }

    public function api(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'prediction' => $this->predictionService->predictGoalAchievement($user),
            'plateau' => $this->predictionService->detectPlateau($user),
            'progress' => $this->predictionService->getGoalProgress($user),
        ]);
    }
}
