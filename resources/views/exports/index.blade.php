<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>数据导出 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; flex-direction: column; }
        }
        @media (max-width: 767px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: none !important; }
        }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen pb-20">
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">数据导出</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4 space-y-4" x-data="exportPage()">
            <!-- Report Export -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-medium mb-3">报表导出 (CSV)</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">数据类型</label>
                        <select x-model="reportType" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                            <option value="meals">饮食记录</option>
                            <option value="exercises">运动记录</option>
                            <option value="weights">体重记录</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">开始日期</label>
                            <input type="date" x-model="dateStart" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">结束日期</label>
                            <input type="date" x-model="dateEnd" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                    <button @click="exportReport()" :disabled="exporting"
                            class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span x-show="!exporting">导出CSV</span>
                        <span x-show="exporting">导出中...</span>
                    </button>
                </div>
            </div>

            <!-- Full Data Export -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-medium mb-3">完整数据导出 (JSON)</h3>
                <p class="text-sm text-gray-500 mb-3">导出您的所有数据，包括档案、饮食记录、运动记录、体重记录等。完成后将通知您下载。</p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-700 mb-3">
                    <p>导出文件有效期7天，请及时下载。</p>
                </div>
                <button @click="requestFullExport()" :disabled="fullExporting"
                        class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50">
                    <span x-show="!fullExporting">申请完整导出</span>
                    <span x-show="fullExporting">提交中...</span>
                </button>
                <div x-show="fullExportDone" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    <p>导出已提交，请等待完成后下载。</p>
                </div>
            </div>

            <!-- PIPL Notice -->
            <div class="bg-gray-100 rounded-xl p-4 text-xs text-gray-500">
                <p class="font-medium text-gray-600 mb-1">数据可携权说明</p>
                <p>根据《个人信息保护法》，您有权导出自己的个人数据。导出数据以JSON格式提供，包含您在本应用中的所有个人信息。</p>
            </div>
        </div>
    </div>

    <script>
        function exportPage() {
            return {
                reportType: 'meals',
                dateStart: '{{ now()->subWeek()->format("Y-m-d") }}',
                dateEnd: '{{ now()->format("Y-m-d") }}',
                exporting: false,
                fullExporting: false,
                fullExportDone: false,

                async exportReport() {
                    this.exporting = true;
                    try {
                        const response = await fetch('{{ route("exports.report") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'text/csv'
                            },
                            body: JSON.stringify({
                                type: this.reportType,
                                date_range: this.dateStart + '..' + this.dateEnd
                            })
                        });

                        if (response.ok) {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = this.reportType + '_export.csv';
                            a.click();
                            window.URL.revokeObjectURL(url);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.exporting = false;
                },

                async requestFullExport() {
                    this.fullExporting = true;
                    try {
                        const response = await fetch('{{ route("exports.fullData") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.fullExportDone = true;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.fullExporting = false;
                }
            }
        }
    </script>
    </div>
</body>
</html>
