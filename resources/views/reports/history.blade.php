<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>报告历史 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
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
    </div>
</body>
</html>
