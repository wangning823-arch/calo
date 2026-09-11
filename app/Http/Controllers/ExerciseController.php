<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseStoreRequest;
use App\Models\ExerciseRecord;
use App\Models\ExerciseType;
use App\Services\ExerciseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(
        private ExerciseService $exerciseService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $date = $this->normalizeDate($request->query('date')) ?? now()->toDateString();

        $records = ExerciseRecord::where('user_id', $user->id)
            ->with('exerciseType')
            ->whereDate('date', $date)
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('exercises.index', compact('user', 'records', 'date'));
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $exerciseTypes = ExerciseType::orderBy('category')->orderBy('name')->get();
        $categories = $exerciseTypes->pluck('category')->unique()->values();
        $now = now()->format('H:i');

        return view('exercises.create', compact('user', 'exerciseTypes', 'categories', 'now'));
    }

    public function store(ExerciseStoreRequest $request)
    {
        $data = $request->validated();
        $recordedAt = $this->buildRecordedAt($data['date'] ?? null, $data['recorded_time'] ?? null);
        $data['recorded_at'] = $recordedAt;
        unset($data['recorded_time']);

        $this->exerciseService->recordExercise($request->user(), $data);

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

        $data = $request->validated();

        if (isset($data['recorded_time'])) {
            $date = $record->date->toDateString();
            $data['recorded_at'] = $this->buildRecordedAt($date, $data['recorded_time']);
        }
        unset($data['recorded_time']);

        $this->exerciseService->updateRecord($record, $data);

        return redirect()->route('dashboard')
            ->with('success', '运动记录已更新！');
    }

    public function destroy(Request $request, ExerciseRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->exerciseService->deleteRecord($record);

        return redirect()->route('exercises.index', ['date' => $record->date->toDateString()])
            ->with('success', '运动记录已删除。');
    }

    public function quickExercise(Request $request)
    {
        $presets = [
            'run5'  => ['exercise_type_id' => 1, 'duration_minutes' => 30, 'distance_km' => 5, 'label' => '5km慢跑'],
            'run10' => ['exercise_type_id' => 1, 'duration_minutes' => 60, 'distance_km' => 10, 'label' => '10km慢跑'],
            'strength' => ['exercise_type_id' => 42, 'duration_minutes' => 60, 'distance_km' => null, 'label' => '力量训练1小时'],
        ];

        $preset = $presets[$request->input('type')] ?? null;
        if (!$preset) {
            return response()->json(['error' => '无效的快捷运动类型'], 422);
        }

        $now = now();
        $data = [
            'exercise_type_id' => $preset['exercise_type_id'],
            'duration_minutes' => $preset['duration_minutes'],
            'distance_km' => $preset['distance_km'],
            'recorded_at' => $now->toDateTimeString(),
            'date' => $now->toDateString(),
        ];

        $this->exerciseService->recordExercise($request->user(), $data);

        return response()->json(['success' => true, 'message' => $preset['label'] . '已记录！']);
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

    private function buildRecordedAt(?string $date, ?string $time): string
    {
        $date = $date ?? now()->toDateString();
        $time = $time ?? now()->format('H:i');

        return Carbon::parse("{$date} {$time}")->toDateTimeString();
    }
}
