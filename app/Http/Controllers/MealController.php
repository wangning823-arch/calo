<?php

namespace App\Http\Controllers;

use App\Models\MealRecord;
use App\Services\FoodService;
use App\Services\MealService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function __construct(
        private MealService $mealService,
        private FoodService $foodService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $date = $this->normalizeDate($request->query('date')) ?? now()->toDateString();

        $records = MealRecord::where('user_id', $user->id)
            ->with('food')
            ->whereDate('date', $date)
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $mealTypes = ['breakfast' => '早餐', 'lunch' => '午餐', 'dinner' => '晚餐', 'snack' => '加餐'];

        return view('meals.index', compact('user', 'records', 'mealTypes', 'date'));
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $popularFoods = $this->foodService->getPopularFoods($user->id, 10);
        $mealType = $request->input('meal_type', $this->getDefaultMealType());
        $today = now()->toDateString();
        $now = now()->format('H:i');

        return view('meals.create', compact('user', 'popularFoods', 'mealType', 'today', 'now'));
    }

    private function getDefaultMealType(): string
    {
        $hour = Carbon::now('Asia/Shanghai')->hour;

        if ($hour >= 6 && $hour < 10) {
            return 'breakfast';
        }

        if ($hour >= 11 && $hour < 14) {
            return 'lunch';
        }

        if ($hour >= 17 && $hour < 20) {
            return 'dinner';
        }

        return 'snack';
    }

    private function buildRecordedAt(?string $date, ?string $time): string
    {
        $date = $date ?? now()->toDateString();
        $time = $time ?? now()->format('H:i');

        return Carbon::parse("{$date} {$time}")->toDateTimeString();
    }

    public function store(Request $request)
    {
        $request->validate([
            'food_id' => ['required', 'exists:food_items,id'],
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'serving_grams' => ['required', 'numeric', 'min:1', 'max:5000'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'recorded_time' => ['nullable', 'date_format:H:i'],
        ]);

        $recordedAt = $this->buildRecordedAt($request->input('date'), $request->input('recorded_time'));

        $this->mealService->recordMeal($request->user()->id, array_merge(
            $request->only(['food_id', 'meal_type', 'serving_grams', 'notes']),
            ['recorded_at' => $recordedAt],
        ));

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard')
            ->with('success', '饮食记录已保存。');
    }

    public function edit(Request $request, MealRecord $meal)
    {
        if ($meal->user_id !== $request->user()->id) {
            abort(403);
        }

        $popularFoods = $this->foodService->getPopularFoods($request->user()->id, 10);

        return view('meals.edit', ['meal' => $meal, 'popularFoods' => $popularFoods]);
    }

    public function update(Request $request, MealRecord $meal)
    {
        if ($meal->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'food_id' => ['required', 'exists:food_items,id'],
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'serving_grams' => ['required', 'numeric', 'min:1', 'max:5000'],
            'recorded_time' => ['nullable', 'date_format:H:i'],
        ], [
            'serving_grams.required' => '请填写饮食份量（克）。',
            'serving_grams.numeric' => '饮食份量必须是数字。',
            'serving_grams.min' => '饮食份量不能少于1克。',
            'serving_grams.max' => '饮食份量不能超过5000克。',
            'meal_type.required' => '请选择餐次。',
            'meal_type.in' => '餐次无效。',
            'food_id.required' => '请选择食物。',
            'food_id.exists' => '所选食物不存在。',
        ]);

        $data = $request->only(['food_id', 'meal_type', 'serving_grams', 'notes']);

        if ($request->has('recorded_time')) {
            $date = $meal->date->toDateString();
            $data['recorded_at'] = $this->buildRecordedAt($date, $request->input('recorded_time'));
        }

        $this->mealService->updateRecord($meal->id, $data, $request->user()->id);

        return redirect()->route('dashboard')
            ->with('success', '记录已更新。');
    }

    public function destroy(Request $request, MealRecord $meal)
    {
        if ($meal->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->mealService->deleteRecord($meal->id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('meals.index', ['date' => $meal->date->toDateString()])
            ->with('success', '记录已删除。');
    }

    public function copyYesterday(Request $request, string $mealType)
    {
        $record = $this->mealService->copyYesterdayMeal($request->user()->id, $mealType);

        if (! $record) {
            return back()->with('error', '昨日没有该餐次的记录。');
        }

        return redirect()->route('dashboard')
            ->with('success', '已复制昨日记录。');
    }

    public function dailySummary(Request $request, string $date): JsonResponse
    {
        $summary = $this->mealService->getDailySummary($request->user()->id, $date);

        return response()->json($summary);
    }
}
