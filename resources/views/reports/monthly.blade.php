<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>月报 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
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
                <h1 class="text-lg font-semibold">月报</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <div class="text-sm text-gray-500 mb-1">{{ $report['period'] }}</div>
                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($report['total_intake'], 0) }}</div>
                        <div class="text-xs text-gray-500">总摄入 kcal</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-{{ $report['avg_deficit'] >= 0 ? 'green' : 'red' }}-600">{{ $report['avg_deficit'] >= 0 ? '-' : '+' }}{{ number_format(abs($report['avg_deficit']), 0) }}</div>
                        <div class="text-xs text-gray-500">日均缺口 kcal</div>
                    </div>
                </div>
            </div>

            @if($report['weight_change'] !== null)
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">体重变化</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-lg font-bold">{{ $report['weight_start'] }} kg</span>
                        <span class="text-xs text-gray-400">→</span>
                        <span class="text-lg font-bold">{{ $report['weight_end'] }} kg</span>
                    </div>
                    <span class="text-sm font-medium {{ $report['weight_change'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $report['weight_change'] > 0 ? '+' : '' }}{{ $report['weight_change'] }} kg
                    </span>
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">每周趋势</h3>
                <div class="space-y-3">
                    @foreach($report['weeks'] as $i => $week)
                    <div class="border-b border-gray-100 pb-3 last:border-0">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">第{{ $i + 1 }}周</span>
                            <span class="font-medium">日均 {{ number_format($week['avg_intake'], 0) }} kcal</span>
                        </div>
                        @if($week['weight_change'] !== null)
                        <div class="text-xs text-{{ $week['weight_change'] <= 0 ? 'green' : 'red' }}-600 mt-1">
                            体重 {{ $week['weight_change'] > 0 ? '+' : '' }}{{ $week['weight_change'] }} kg
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">记录完成率</h3>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $report['completion_rate'] }}%"></div>
                    </div>
                    <span class="text-sm font-medium">{{ $report['completion_rate'] }}%</span>
                </div>
                <div class="text-xs text-gray-400 mt-1">{{ $report['total_days_with_records'] }}/{{ $report['total_days'] }} 天有记录</div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
