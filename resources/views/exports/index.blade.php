<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>数据导出 - Calo</title>
    @include('partials.app-scripts')
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
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-[var(--calo-muted)]">
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
            <div class="card p-4">
                <h3 class="font-medium mb-3">报表导出 (CSV)</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-[var(--calo-muted)] mb-1">数据类型</label>
                        <select x-model="reportType" class="input-field text-sm">
                            <option value="meals">饮食记录</option>
                            <option value="exercises">运动记录</option>
                            <option value="weights">体重记录</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-[var(--calo-muted)] mb-1">开始日期</label>
                            <input type="date" x-model="dateStart" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-sm text-[var(--calo-muted)] mb-1">结束日期</label>
                            <input type="date" x-model="dateEnd" class="input-field text-sm">
                        </div>
                    </div>
                    <button @click="exportReport()" :disabled="exporting"
                            class="btn-primary w-full py-3 text-sm disabled:opacity-50">
                        <span x-show="!exporting">导出CSV</span>
                        <span x-show="exporting">导出中...</span>
                    </button>
                </div>
            </div>

            <!-- Full Data Export -->
            <div class="card p-4">
                <h3 class="font-medium mb-3">完整数据导出 (JSON)</h3>
                <p class="text-sm text-[var(--calo-muted)] mb-3">导出您的所有数据，包括档案、饮食记录、运动记录、体重记录等。完成后将通知您下载。</p>
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-3 text-sm text-amber-800 dark:text-amber-200 mb-3">
                    <p>导出文件有效期7天，请及时下载。</p>
                </div>
                <button @click="requestFullExport()" :disabled="fullExporting"
                        class="btn-primary w-full py-3 text-sm disabled:opacity-50">
                    <span x-show="!fullExporting">申请完整导出</span>
                    <span x-show="fullExporting">提交中...</span>
                </button>
                <div x-show="fullExportDone" class="mt-3 p-3 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-sm text-brand-800 dark:text-brand-200 shadow-soft">
                    <p>导出已提交，请等待完成后下载。</p>
                </div>
            </div>

            <!-- PIPL Notice -->
            <div class="bg-black/[0.03] dark:bg-white/[0.04] rounded-xl p-4 text-xs text-[var(--calo-muted)]">
                <p class="font-medium text-[var(--calo-muted)] mb-1">数据可携权说明</p>
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
</body>
</html>
