<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>周报 - Calo</title>
    @include('partials.app-scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
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
                <h1 class="text-lg font-semibold">周报</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            <div class="card p-4 mb-4">
                <div class="text-sm text-[var(--calo-muted)] mb-1">{{ $report['period'] }}</div>
                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ number_format($report['avg_intake'], 0) }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">日均摄入 kcal</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-{{ $report['avg_deficit'] >= 0 ? 'green' : 'red' }}-600 dark:text-{{ $report['avg_deficit'] >= 0 ? 'green' : 'red' }}-400">{{ $report['avg_deficit'] >= 0 ? '' : '+' }}{{ number_format(abs($report['avg_deficit']), 0) }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">日均实际缺口 kcal</div>
                    </div>
                </div>
                @if($report['maintenance'] || $report['target_deficit'])
                <div class="grid grid-cols-2 gap-4 mt-3 pt-3 border-t border-[var(--calo-line)]">
                    <div class="text-center">
                        <div class="text-lg font-bold">{{ $report['maintenance'] ? number_format($report['maintenance'], 0) : '—' }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">平衡热量</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-orange-500 dark:text-orange-300">{{ $report['target_deficit'] !== null ? '−'.number_format($report['target_deficit'], 0) : '—' }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">预期缺口</div>
                    </div>
                </div>
                @endif
            </div>

            @if($report['weight_change'] !== null)
            <div class="card p-4 mb-4">
                <h3 class="text-sm font-medium text-[var(--calo-muted)] mb-2">体重变化</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-lg font-bold">{{ $report['weight_start'] }} kg</span>
                        <span class="text-xs text-[var(--calo-muted)]">→</span>
                        <span class="text-lg font-bold">{{ $report['weight_end'] }} kg</span>
                    </div>
                    <span class="text-sm font-medium {{ $report['weight_change'] <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $report['weight_change'] > 0 ? '+' : '' }}{{ $report['weight_change'] }} kg
                    </span>
                </div>
            </div>
            @endif

            <div class="card p-4 mb-4">
                <h3 class="text-sm font-medium text-[var(--calo-muted)] mb-2">每日摄入</h3>
                <div id="intake-chart" style="height: 200px;"></div>
            </div>

            <div class="card p-4 mb-4">
                <h3 class="text-sm font-medium text-[var(--calo-muted)] mb-2">营养素结构</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $report['protein_pct'] }}%</div>
                        <div class="text-xs text-[var(--calo-muted)]">蛋白质</div>
                        <div class="text-xs text-[var(--calo-muted)]">{{ number_format($report['protein_total'], 0) }}g</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ $report['carbs_pct'] }}%</div>
                        <div class="text-xs text-[var(--calo-muted)]">碳水</div>
                        <div class="text-xs text-[var(--calo-muted)]">{{ number_format($report['carbs_total'], 0) }}g</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ $report['fat_pct'] }}%</div>
                        <div class="text-xs text-[var(--calo-muted)]">脂肪</div>
                        <div class="text-xs text-[var(--calo-muted)]">{{ number_format($report['fat_total'], 0) }}g</div>
                    </div>
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h3 class="text-sm font-medium text-[var(--calo-muted)] mb-2">记录完成率</h3>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-black/[0.06] dark:bg-white/[0.08] rounded-full h-3">
                        <div class="bg-brand-600 h-3 rounded-full" style="width: {{ $report['completion_rate'] }}%"></div>
                    </div>
                    <span class="text-sm font-medium">{{ $report['completion_rate'] }}%</span>
                </div>
                <div class="text-xs text-[var(--calo-muted)] mt-1">{{ $report['days_with_records'] }}/{{ $report['days_count'] }} 天有记录</div>
            </div>

            <div class="card p-4 mb-4">
                <h3 class="text-sm font-medium text-[var(--calo-muted)] mb-2">健康减重天数</h3>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $report['healthy_loss_days'] }} <span class="text-sm font-normal text-[var(--calo-muted)]">/ {{ $report['days_count'] }} 天</span></div>
                <div class="text-xs text-[var(--calo-muted)]">日均实际缺口 300–750 kcal 为健康减重</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chart = echarts.init(document.getElementById('intake-chart'));
            const stats = @json($report['daily_stats']);
            const budget = @json($report['budget']);

            chart.setOption({
                tooltip: { trigger: 'axis' },
                xAxis: {
                    type: 'category',
                    data: stats.map(s => s.date.substring(5)),
                    axisLabel: { fontSize: 10 }
                },
                yAxis: {
                    type: 'value',
                    name: 'kcal',
                    nameTextStyle: { fontSize: 10 }
                },
                series: [
                    {
                        name: '摄入',
                        type: 'bar',
                        data: stats.map(s => s.intake),
                        itemStyle: { color: '#059669' },
                        barWidth: '40%'
                    },
                    {
                        name: '消耗',
                        type: 'bar',
                        data: stats.map(s => s.burned),
                        itemStyle: { color: '#f59e0b' },
                        barWidth: '40%'
                    },
                    {
                        name: '目标摄入',
                        type: 'line',
                        data: stats.map(() => budget),
                        lineStyle: { type: 'dashed', color: '#ef4444' },
                        symbol: 'none'
                    }
                ],
                grid: { left: 50, right: 10, top: 30, bottom: 30 }
            });
        });
    </script>
</body>
</html>
