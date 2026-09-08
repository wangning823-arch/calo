<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function weekly(Request $request, string $date): \Illuminate\View\View
    {
        $userId = $request->user()->id;
        $report = $this->reportService->generateWeeklyReport($userId, $date);
        $history = $this->reportService->getReportHistory($userId);

        return view('reports.weekly', compact('report', 'history'));
    }

    public function monthly(Request $request, string $date): \Illuminate\View\View
    {
        $userId = $request->user()->id;
        $report = $this->reportService->generateMonthlyReport($userId, $date);
        $history = $this->reportService->getReportHistory($userId);

        return view('reports.monthly', compact('report', 'history'));
    }

    public function history(Request $request): \Illuminate\View\View
    {
        $history = $this->reportService->getReportHistory($request->user()->id);

        return view('reports.history', compact('history'));
    }
}
