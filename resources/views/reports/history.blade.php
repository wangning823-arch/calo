<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>报告历史 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">报告历史</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            @if(empty($history['weekly']) && empty($history['monthly']))
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="text-gray-400 mb-2">暂无报告</div>
                <div class="text-sm text-gray-500">记录饮食数据后即可生成报告</div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="font-medium mb-3">周报</h3>
                <div class="space-y-2">
                    @foreach($history['weekly'] as $report)
                    <a href="{{ route('reports.weekly', $report['period']) }}" class="block p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                        <div class="text-sm font-medium">{{ $report['label'] }}</div>
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="font-medium mb-3">月报</h3>
                <div class="space-y-2">
                    @foreach($history['monthly'] as $report)
                    <a href="{{ route('reports.monthly', $report['period']) }}" class="block p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                        <div class="text-sm font-medium">{{ $report['label'] }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
