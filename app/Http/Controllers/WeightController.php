<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeightStoreRequest;
use App\Models\WeightRecord;
use App\Services\WeightService;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    public function __construct(
        private WeightService $weightService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $date = $this->normalizeDate($request->query('date')) ?? now()->toDateString();

        $records = WeightRecord::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('weights.index', compact('user', 'records', 'date'));
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
        $latestWeight = $this->weightService->getLatestWeight($user);
        $recentRecords = WeightRecord::where('user_id', $user->id)
            ->latest('date')
            ->limit(30)
            ->get();

        return view('weights.create', compact('user', 'latestWeight', 'recentRecords'));
    }

    public function store(WeightStoreRequest $request)
    {
        try {
            $this->weightService->recordWeight($request->user(), $request->validated());

            return redirect()->route('dashboard')
                ->with('success', '体重记录已保存！');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['weight' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, WeightRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $user = $request->user();

        return view('weights.edit', compact('record', 'user'));
    }

    public function update(WeightStoreRequest $request, WeightRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->weightService->updateRecord($record, $request->validated());

            return redirect()->route('weights.trend')
                ->with('success', '体重记录已更新！');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['weight' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, WeightRecord $record)
    {
        if ($record->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->weightService->deleteRecord($record);

        return redirect()->route('weights.index', ['date' => $record->date->toDateString()])
            ->with('success', '体重记录已删除。');
    }

    public function trend(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        $trend = $this->weightService->getWeightTrend($user, $period);
        $latestWeight = $this->weightService->getLatestWeight($user);

        return view('weights.trend', compact('user', 'trend', 'latestWeight'));
    }

    public function trendApi(Request $request)
    {
        $period = $request->get('period', 'month');
        $trend = $this->weightService->getWeightTrend($request->user(), $period);

        return response()->json($trend);
    }
}
