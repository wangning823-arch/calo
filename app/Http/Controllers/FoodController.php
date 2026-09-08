<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Services\FoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function __construct(
        private FoodService $foodService,
    ) {}

    public function create()
    {
        return view('foods.create');
    }

    public function manage(Request $request)
    {
        $foods = $this->foodService->getCustomFoods($request->user()->id);

        return view('foods.manage', compact('foods'));
    }

    public function edit(Request $request, FoodItem $food)
    {
        if ($food->user_id !== $request->user()->id || ! $food->is_user_custom) {
            abort(403);
        }

        return view('foods.edit', compact('food'));
    }

    public function update(Request $request, FoodItem $food)
    {
        if ($food->user_id !== $request->user()->id || ! $food->is_user_custom) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'calories_per_100g' => ['required', 'numeric', 'min:0', 'max:10000'],
            'calorie_unit' => ['nullable', 'in:kcal,kj'],
            'protein_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'carbs_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fat_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $data = $request->all();
        if (($data['calorie_unit'] ?? 'kcal') === 'kj') {
            $data['calories_per_100g'] = round($data['calories_per_100g'] / 4.184, 1);
        }

        $this->foodService->updateCustomFood($request->user()->id, $food->id, $data);

        return redirect()->route('foods.manage')
            ->with('success', '食物已更新。');
    }

    public function destroy(Request $request, FoodItem $food)
    {
        if ($food->user_id !== $request->user()->id || ! $food->is_user_custom) {
            abort(403);
        }

        $this->foodService->deleteCustomFood($request->user()->id, $food->id);

        return redirect()->route('foods.manage')
            ->with('success', '食物已删除。');
    }

    public function index(Request $request)
    {
        $categories = $this->foodService->getCategories();
        $category = $request->input('category');
        $foods = $category
            ? $this->foodService->getByCategory($category, $request->user()->id)
            : collect();

        return view('foods.index', compact('categories', 'foods', 'category'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $results = $query
            ? $this->foodService->search($query, $request->user()->id)
            : collect();

        return view('foods.search', compact('query', 'results'));
    }

    public function show(FoodItem $food)
    {
        $isFavorite = $this->foodService->isFavorite(
            auth()->id(),
            $food->id
        );

        return view('foods.show', compact('food', 'isFavorite'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'calories_per_100g' => ['required', 'numeric', 'min:0', 'max:10000'],
            'calorie_unit' => ['nullable', 'in:kcal,kj'],
            'protein_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'carbs_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fat_per_100g' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $data = $request->all();

        // Convert kJ to kcal if needed (1 kcal = 4.184 kJ)
        if (($data['calorie_unit'] ?? 'kcal') === 'kj') {
            $data['calories_per_100g'] = round($data['calories_per_100g'] / 4.184, 1);
        }

        $food = $this->foodService->createCustomFood($request->user()->id, $data);

        return redirect()->route('foods.show', $food)
            ->with('success', '自定义食物已创建。');
    }

    public function favorites(Request $request)
    {
        $favorites = $this->foodService->getFavorites($request->user()->id);

        return view('foods.favorites', compact('favorites'));
    }

    public function toggleFavorite(FoodItem $food)
    {
        $added = $this->foodService->toggleFavorite(auth()->id(), $food->id);

        if (request()->expectsJson()) {
            return response()->json(['added' => $added]);
        }

        return back()->with('success', $added ? '已添加到收藏。' : '已取消收藏。');
    }

    public function apiSearch(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $results = $this->foodService->search($query, $request->user()->id ?? null);

        return response()->json([
            'data' => $results->map(fn ($food) => [
                'id' => $food->id,
                'name' => $food->name,
                'category' => $food->category,
                'calories_per_100g' => $food->calories_per_100g,
                'protein_per_100g' => $food->protein_per_100g,
                'carbs_per_100g' => $food->carbs_per_100g,
                'fat_per_100g' => $food->fat_per_100g,
                'serving_size' => $food->serving_size,
                'serving_unit' => $food->serving_unit,
            ]),
        ]);
    }
}
