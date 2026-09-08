<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseStoreRequest;
use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Services\ExerciseService;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(
        private ExerciseService $exerciseService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $records = ExerciseRecord::where('user_id', $user->id)
            ->with('exerciseType')
            ->latest('date')
            ->latest('id')
            ->paginate(20);

        return view('exercises.index', compact('user', 'records'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $exerciseTypes = ExerciseType::orderBy('category')->orderBy('name')->get();
        $categories = $exerciseTypes->pluck('category')->unique()->values();

        return view('exercises.create', compact('user', 'exerciseTypes', 'categories'));
    }

    public function store(ExerciseStoreRequest $request)
    {
        $this->exerciseService->recordExercise($request->user(), $request->validated());

        return redirect()->route('dashboard')
            ->with('success', '运动记录已保存！');
    }

    public function edit(Request $request, ExerciseRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $record->load('exerciseType');
        $exerciseTypes = ExerciseType::orderBy('category')->orderBy('name')->get();
        $categories = $exerciseTypes->pluck('category')->unique()->values();
        $user = $request->user();

        return view('exercises.edit', compact('record', 'exerciseTypes', 'categories', 'user'));
    }

    public function update(ExerciseStoreRequest $request, ExerciseRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->exerciseService->updateRecord($record, $request->validated());

        return redirect()->route('dashboard')
            ->with('success', '运动记录已更新！');
    }

    public function destroy(Request $request, ExerciseRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->exerciseService->deleteRecord($record);

        return redirect()->route('dashboard')
            ->with('success', '运动记录已删除。');
    }

    public function apiExerciseTypes(Request $request)
    {
        $query = ExerciseType::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json($query->orderBy('category')->orderBy('name')->get());
    }
}
