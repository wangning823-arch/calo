<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $exportService,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('exports.index');
    }

    public function report(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:meals,exercises,weights'],
            'date_range' => ['required', 'string'],
        ]);

        $userId = $request->user()->id;
        $path = $this->exportService->exportReport($userId, $request->type, $request->date_range);

        return Storage::disk('local')->download($path, basename($path));
    }

    public function fullData(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = $this->exportService->requestFullDataExport($request->user()->id);

        return response()->json([
            'message' => '数据导出已提交，完成后将通知您。',
            'token' => $token,
            'expires_in' => 7 * 24 * 3600,
        ]);
    }

    public function download(string $token)
    {
        $path = $this->exportService->getExportPath($token);

        if (! $path || $this->exportService->isExportExpired($path)) {
            abort(404, '导出文件不存在或已过期。');
        }

        return Storage::disk('local')->download($path, basename($path));
    }
}
