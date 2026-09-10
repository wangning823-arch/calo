<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>体重趋势 - Calo</title>
    @include('partials.app-scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">体重趋势</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <!-- Period selector -->
        <div class="px-4 mt-4" x-data="{ period: '{{ $trend['period'] }}' }">
            <div class="flex gap-2 card p-1">
                <a href="?period=week" class="flex-1 py-2 text-sm text-center rounded-lg transition-colors font-medium"
                   :class="period === 'week' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">7天</a>
                <a href="?period=month" class="flex-1 py-2 text-sm text-center rounded-lg transition-colors font-medium"
                   :class="period === 'month' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">30天</a>
                <a href="?period=quarter" class="flex-1 py-2 text-sm text-center rounded-lg transition-colors font-medium"
                   :class="period === 'quarter' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">90天</a>
            </div>
        </div>

        <!-- Chart -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <div id="weight-chart" style="height: 280px;"></div>
            </div>
        </div>

        <!-- Latest info -->
        @if($latestWeight)
        <div class="px-4 mt-4">
            <div class="card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-[var(--calo-muted)]">当前体重</div>
                        <div class="text-2xl font-bold text-brand-700 dark:text-brand-400">
                            {{ number_format($latestWeight['weight_display'], 1) . ' kg' }}
                        </div>
                    </div>
                    <a href="{{ route('weights.create') }}" class="btn-primary px-4 py-2 text-sm">
                        记录体重
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Tip -->
        <div class="px-4 mt-4 mb-4">
            <div class="rounded-xl bg-black/[0.04] dark:bg-white/[0.06] p-4 text-xs text-[var(--calo-muted)]">
                <p class="font-medium text-[var(--calo-ink)] mb-1">体重波动说明</p>
                <p>单日波动±1kg属正常现象（水分、饮食等因素影响）。请关注7日移动平均线趋势，连续2周以上无变化可能进入平台期。</p>
            </div>
        </div>

        <!-- Recent records -->
        @if($trend['records']->count() > 0)
        <div class="px-4 mt-4 mb-4">
            <div class="card p-4">
                <h3 class="text-sm font-medium mb-3">记录列表</h3>
                <div class="divide-y divide-[var(--calo-line)]">
                    @foreach($trend['records']->reverse()->take(10) as $record)
                        <div class="flex items-center justify-between py-2 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <span class="text-sm text-[var(--calo-muted)]">{{ $record['date'] }}</span>
                            <span class="text-sm font-semibold">{{ $record['weight_display'] }} kg</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        const chartData = @json($trend['records']);
        const movingAvg = @json($trend['moving_average']);

        if (chartData.length > 0) {
            const chart = echarts.init(document.getElementById('weight-chart'));
            const unitLabel = 'kg';

            chart.setOption({
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        let result = params[0].axisValue + '<br/>';
                        params.forEach(p => {
                            result += p.marker + p.seriesName + ': ' + p.value + unitLabel + '<br/>';
                        });
                        return result;
                    }
                },
                legend: {
                    data: ['体重', '7日均线'],
                    bottom: 0,
                    textStyle: { fontSize: 11 }
                },
                grid: {
                    left: '3%', right: '4%', bottom: '15%', top: '5%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: chartData.map(d => d.date),
                    axisLabel: { fontSize: 10 }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}',
                        fontSize: 10
                    },
                    min: function(value) { return Math.floor(value.min - 1); },
                    max: function(value) { return Math.ceil(value.max + 1); }
                },
                series: [
                    {
                        name: '体重',
                        type: 'scatter',
                        data: chartData.map(d => d.weight_display),
                        symbolSize: 6,
                        itemStyle: { color: '#059669' }
                    },
                    {
                        name: '7日均线',
                        type: 'line',
                        data: movingAvg,
                        smooth: true,
                        lineStyle: { color: '#f59e0b', width: 2 },
                        itemStyle: { color: '#f59e0b' },
                        showSymbol: false
                    }
                ]
            });

            window.addEventListener('resize', () => chart.resize());
        }
    </script>
</body>
</html>
