<?php

namespace App\Http\Controllers;

use App\Services\ContentService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService,
    ) {}

    public function recipes(Request $request): \Illuminate\View\View
    {
        $userId = $request->user()->id;

        $goal = \App\Models\WeightGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        $remaining = $goal ? (float) $goal->daily_calorie_budget : null;
        $recipes = $this->contentService->getRecipes($userId, $remaining);

        return view('content.recipes', compact('recipes'));
    }

    public function recipe(int $id): \Illuminate\View\View
    {
        $recipe = $this->contentService->getRecipe($id);
        if (! $recipe) {
            abort(404);
        }

        return view('content.recipe-detail', compact('recipe'));
    }

    public function importRecipe(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
        ]);

        $success = $this->contentService->importRecipeToMeal($request->user()->id, $id, $request->meal_type);

        if ($success) {
            return redirect()->route('dashboard')
                ->with('success', '食谱已导入到饮食记录。');
        }

        return back()->withErrors(['error' => '导入失败，请重试。']);
    }

    public function trainingPlans(Request $request): \Illuminate\View\View
    {
        $plans = $this->contentService->getTrainingPlans($request->user()->id);

        return view('content.training-plans', compact('plans'));
    }

    public function planDetail(int $id): \Illuminate\View\View
    {
        $plan = $this->contentService->getTrainingPlan($id);
        if (! $plan) {
            abort(404);
        }

        return view('content.plan-detail', compact('plan'));
    }

    public function adoptPlan(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $adoption = $this->contentService->adoptPlan($request->user()->id, $id);

        if ($adoption) {
            return redirect()->route('dashboard')
                ->with('success', '训练计划已采用！');
        }

        return back()->withErrors(['error' => '采用失败，请重试。']);
    }

    public function checkIn(Request $request, int $adoptionId): \Illuminate\Http\RedirectResponse
    {
        $success = $this->contentService->checkIn($request->user()->id, $adoptionId);

        if ($success) {
            return back()->with('success', '打卡成功！');
        }

        return back()->withErrors(['error' => '打卡失败，请重试。']);
    }
}
