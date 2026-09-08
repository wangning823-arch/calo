<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>训练计划 - Calo</title>
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
                <h1 class="text-lg font-semibold">训练计划</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            @if(empty($plans))
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="text-gray-400 mb-2">暂无训练计划</div>
                <div class="text-sm text-gray-500">管理员正在添加更多计划</div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($plans as $plan)
                <a href="{{ route('content.planDetail', $plan['id']) }}" class="block bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ $plan['title'] }}</h3>
                            <div class="flex gap-2 mt-1">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['goal'] === 'lose' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $plan['goal'] === 'lose' ? '减脂' : '塑形' }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['difficulty'] === 'beginner' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $plan['difficulty'] === 'beginner' ? '新手' : '进阶' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            {{ $plan['duration_weeks'] }}周
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    </div>
</body>
</html>
