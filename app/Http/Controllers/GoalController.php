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

        $userService = app(\App\Services\UserService::class);
        $tdee = $userService->calculateTDEE($user);
        $minCalories = $user->gender === 'female' ? 1200 : 1500;
        $maxSafeDeficit = max(0, (int) round($tdee - $minCalories));

        // Prefill from the active goal when editing; otherwise show recommendations
        $defaultTargetWeight = null;
        $defaultTargetDate = null;
        $usingRecommendation = false;

        if ($currentGoal) {
            $defaultTargetWeight = (float) $currentGoal->target_weight;
            $defaultTargetDate = $currentGoal->target_date->format('Y-m-d');
        } elseif ($currentWeight && $user->height) {
            $usingRecommendation = true;
            $heightM = (float) $user->height / 100;
            $idealWeight = 22 * $heightM * $heightM;
            $defaultTargetWeight = round($idealWeight, 1);

            $weightToLose = $currentWeight - $defaultTargetWeight;
            if ($weightToLose > 0) {
                $weeksNeeded = max(1, (int) ceil($weightToLose / 0.5));
                $defaultTargetDate = \Carbon\Carbon::now()->addWeeks($weeksNeeded)->format('Y-m-d');
            }
        }

        return view('goals.create', compact(
            'user', 'currentGoal', 'currentWeight', 'tdee', 'minCalories',
            'defaultTargetWeight', 'defaultTargetDate', 'maxSafeDeficit', 'usingRecommendation'
        ));
    }

    public function store(GoalCreateRequest $request)
    {
        $user = $request->user();

        $latestWeight = $user->weightRecords()->latest('date')->first();
        $currentWeight = $latestWeight ? (float) $latestWeight->weight_kg : null;

        if (!$currentWeight) {
            return back()->withErrors(['target_weight' => '请先记录当前体重。'])->withInput();
        }

        // Validate goal
        $validation = $this->goalService->validateGoal(
            $user,
            (float) $request->target_weight,
            $request->target_date
        );

        if (! $validation['valid']) {
            return back()->withErrors(['target_weight' => $validation['message']])->withInput();
        }

        // Check if deficit is too extreme
        $calc = $this->goalService->calculateRequiredDeficit(
            $currentWeight,
            (float) $request->target_weight,
            $request->target_date
        );

        // Check if planned deficit exceeds the max safe deficit
        $budgetCheck = $this->goalService->calculateDailyBudget($user, $calc['daily_deficit']);
        if ($budgetCheck['is_over_safe']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '按您的平衡热量 '.$budgetCheck['tdee'].' kcal 与最低安全摄入 '.$budgetCheck['min_calories'].' kcal，'
                    . '最大安全缺口约 '.$budgetCheck['max_safe_deficit'].' kcal/天。'
                    . '当前目标需每日缺口 '.$budgetCheck['planned_deficit'].' kcal（目标摄入 '.$budgetCheck['budget'].' kcal），可能偏激进。'
                    . '仍可继续，系统会按您设定的数值生效；否则建议延长目标日期至 '
                    . \Carbon\Carbon::now()->addDays(max(1, ceil(($calc['weight_to_lose'] * 7700) / max(1, $budgetCheck['max_safe_deficit']))))->format('Y年m月d日')
                    . ' 附近。是否继续？';
                return back()->with('warning', $warning)->withInput();
            }
        } elseif ($calc['is_extreme']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '当前计划周均减重 '.$calc['weekly_rate'].' kg，超过安全上限 1.5 kg/周。'
                    . '需每日制造约 '.$calc['daily_deficit'].' kcal 缺口（目标摄入约 '.$budgetCheck['budget'].' kcal），可能影响健康。请确认是否继续。';
                return back()->with('warning', $warning)->withInput();
            }
        } elseif ($calc['is_warning']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '当前计划周均减重 '.$calc['weekly_rate'].' kg，略高于推荐值 1 kg/周。'
                    . '需每日制造约 '.$calc['daily_deficit'].' kcal 缺口（目标摄入约 '.$budgetCheck['budget'].' kcal）。请确认是否继续。';
                return back()->with('warning', $warning)->withInput();
            }
        }

        // Deactivate any existing active goal, then create the updated plan
        $user->activeWeightGoal?->update(['status' => 'completed']);

        $goal = $this->goalService->createGoal($user, $request->validated());

        return redirect()->route('goals.current')
            ->with('success', '减重目标已设定！每日预期缺口 '.$goal->target_deficit.' kcal，目标摄入 '.$goal->daily_calorie_budget.' kcal。');
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
