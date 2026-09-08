<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalCreateRequest;
use App\Models\WeightGoal;
use App\Services\GoalService;
use App\Services\UserService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        private GoalService $goalService,
        private UserService $userService,
    ) {}

    public function create(Request $request)
    {
        $user = $request->user();
        $currentGoal = $this->goalService->getCurrentGoal($user);
        $latestWeight = $user->weightRecords()->latest('date')->first();
        $currentWeight = $latestWeight ? (float) $latestWeight->weight_kg : null;

        return view('goals.create', compact('user', 'currentGoal', 'currentWeight'));
    }

    public function store(GoalCreateRequest $request)
    {
        $user = $request->user();

        // Validate goal
        $validation = $this->goalService->validateGoal(
            $user,
            (float) $request->target_weight,
            $request->target_date
        );

        if (! $validation['valid']) {
            return back()->withErrors(['target_weight' => $validation['message']])->withInput();
        }

        // If warning, check if user confirmed
        if (isset($validation['warning']) && ! $request->boolean('confirm_warning')) {
            return back()->with('warning', $validation['warning'])->withInput();
        }

        // Deactivate any existing active goal
        $user->activeWeightGoal?->update(['status' => 'completed']);

        $goal = $this->goalService->createGoal($user, $request->validated());

        return redirect()->route('goals.current')
            ->with('success', '减重目标已设定！每日热量预算为'.$goal->daily_calorie_budget.'kcal。');
    }

    public function current(Request $request)
    {
        $user = $request->user();
        $goal = $this->goalService->getCurrentGoal($user);

        if (! $goal) {
            return redirect()->route('goals.create');
        }

        $latestWeight = $user->weightRecords()->latest('date')->first();
        $currentWeight = $latestWeight ? (float) $latestWeight->weight_kg : null;

        $progress = null;
        if ($currentWeight && $goal->start_weight > $goal->target_weight) {
            $totalToLose = $goal->start_weight - $goal->target_weight;
            $lost = $goal->start_weight - $currentWeight;
            $progress = max(0, min(100, ($lost / $totalToLose) * 100));
        }

        return view('goals.current', compact('user', 'goal', 'currentWeight', 'progress'));
    }

    public function update(GoalCreateRequest $request, WeightGoal $goal)
    {
        $user = $request->user();

        if ($goal->user_id !== $user->id) {
            abort(403);
        }

        // Re-validate
        $validation = $this->goalService->validateGoal(
            $user,
            (float) $request->input('target_weight', $goal->target_weight),
            $request->input('target_date', $goal->target_date)
        );

        if (! $validation['valid']) {
            return back()->withErrors(['target_weight' => $validation['message']])->withInput();
        }

        $this->goalService->updateGoal($goal, $request->validated());

        return redirect()->route('goals.current')
            ->with('success', '目标已更新。');
    }
}
