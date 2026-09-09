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

        // Calculate smart defaults: ideal weight at BMI 22, date at 1kg/week
        $defaultTargetWeight = null;
        $defaultTargetDate = null;
        if ($currentWeight && $user->height) {
            $heightM = (float) $user->height / 100;
            $idealWeight = 22 * $heightM * $heightM;
            $defaultTargetWeight = round($idealWeight, 1);

            $weightToLose = $currentWeight - $defaultTargetWeight;
            if ($weightToLose > 0) {
                $weeksNeeded = max(1, ceil($weightToLose / 1.0));
                $defaultTargetDate = \Carbon\Carbon::now()->addWeeks($weeksNeeded)->format('Y-m-d');
            }
        }

        return view('goals.create', compact(
            'user', 'currentGoal', 'currentWeight', 'tdee', 'minCalories',
            'defaultTargetWeight', 'defaultTargetDate'
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

        // Check if budget hits the safety floor
        $budgetCheck = $this->goalService->calculateDailyBudget($user, $calc['daily_deficit']);
        if ($budgetCheck['is_floored']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '您的TDEE为' . $budgetCheck['tdee'] . 'kcal，安全最低摄入为' . $budgetCheck['min_calories'] . 'kcal，'
                    . '最大安全缺口仅' . $budgetCheck['actual_deficit'] . 'kcal/天。'
                    . '按此速度完成目标需要更长时间，建议将目标日期设为'
                    . \Carbon\Carbon::now()->addDays(ceil(($calc['weight_to_lose'] * 7700) / $budgetCheck['actual_deficit']))->format('Y年m月d日')
                    . '或减少目标减重量。是否继续？';
                return back()->with('warning', $warning)->withInput();
            }
        } elseif ($calc['is_extreme']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '当前计划周均减重' . $calc['weekly_rate'] . 'kg，超过安全上限(1.5kg/周)。需每日亏空' . $calc['daily_deficit'] . 'kcal，可能影响健康。请确认是否继续。';
                return back()->with('warning', $warning)->withInput();
            }
        } elseif ($calc['is_warning']) {
            if (! $request->boolean('confirm_warning')) {
                $warning = '当前计划周均减重' . $calc['weekly_rate'] . 'kg，略高于推荐值(1kg/周)。每日需亏空' . $calc['daily_deficit'] . 'kcal。请确认是否继续。';
                return back()->with('warning', $warning)->withInput();
            }
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
