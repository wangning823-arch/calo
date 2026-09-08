<?php

namespace App\Http\Controllers;

use App\Models\MealRecord;
use App\Services\FoodService;
use App\Services\MealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function __construct(
        private MealService $mealService,
        private FoodService $foodService,
    ) {}

    public function create(Request $request)
    {
        $user = $request->user();
        $popularFoods = $this->foodService->getPopularFoods($user->id, 10);
        $mealType = $request->input('meal_type', 'lunch');
        $today = now()->toDateString();

        return view('meals.create', compact('user', 'popularFoods', 'mealType', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'food_id' => ['required', 'exists:food_items,id'],
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'serving_grams' => ['required', 'numeric', 'min:1', 'max:5000'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $this->mealService->recordMeal($request->user()->id, $request->only([
            'food_id', 'meal_type', 'serving_grams', 'date', 'notes',
        ]));

        $date = $request->input('date', now()->toDateString());

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
        ]);

        $this->mealService->updateRecord($meal->id, $request->only([
            'food_id', 'meal_type', 'serving_grams', 'notes',
        ]), $request->user()->id);

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

        return redirect()->route('dashboard')
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
