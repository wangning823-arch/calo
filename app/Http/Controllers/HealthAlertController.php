<?php

namespace App\Http\Controllers;

use App\Services\HealthAlertService;
use Illuminate\Http\Request;

class HealthAlertController extends Controller
{
    public function __construct(
        private HealthAlertService $healthAlertService,
    ) {}

    public function check(Request $request)
    {
        $user = $request->user();
        $level = $this->healthAlertService->getAlertLevel($user);

        return response()->json($level);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'choice' => ['required', 'in:acknowledge,adjust'],
        ]);

        $this->healthAlertService->confirmAlert($request->user(), $request->choice);

        return response()->json(['success' => true]);
    }
}
