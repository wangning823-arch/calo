<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>目标预测 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <h1 class="text-lg font-semibold">目标预测</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(!$prediction && !$progress)
            <div class="px-4 mt-8 text-center">
                <div class="text-gray-400 text-lg mb-2">暂无目标数据</div>
                <a href="{{ route('goals.create') }}" class="text-blue-500 text-sm">设定减重目标</a>
            </div>
        @else
            <!-- Progress -->
            @if($progress)
            <div class="px-4 mt-4">
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="text-sm font-medium text-gray-500 mb-3">目标进度</h2>
                    <div class="flex items-center gap-4 mb-3">
                        <div class="flex-1">
                            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $progress['progress'] }}%"></div>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-blue-600">{{ $progress['progress'] }}%</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center text-sm">
                        <div>
                            <div class="text-gray-500">已减</div>
                            <div class="font-bold text-green-600">{{ $progress['lost_jin'] }}斤</div>
                        </div>
                        <div>
                            <div class="text-gray-500">当前</div>
                            <div class="font-bold">{{ $user->unit_preference === 'jin' ? round($progress['current_weight'] * 2, 1) . '斤' : round($progress['current_weight'], 1) . 'kg' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">目标</div>
                            <div class="font-bold text-blue-600">{{ $user->unit_preference === 'jin' ? round($progress['target_weight'] * 2, 1) . '斤' : round($progress['target_weight'], 1) . 'kg' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Prediction -->
            @if($prediction)
            <div class="px-4 mt-4">
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="text-sm font-medium text-gray-500 mb-3">达成预测</h2>
                    @if($prediction['predictable'] ?? false)
                        @if($prediction['achieved'] ?? false)
                            <div class="text-center py-4">
                                <div class="text-3xl mb-2">🎉</div>
                                <div class="text-lg font-bold text-green-600">{{ $prediction['message'] }}</div>
                            </div>
                        @else
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ ($prediction['on_track'] ?? false) ? 'bg-green-100' : 'bg-yellow-100' }}">
                                        <span class="text-lg">{{ ($prediction['on_track'] ?? false) ? '✅' : '⚠️' }}</span>
                                    </div>
                                    <div class="text-sm">{{ $prediction['message'] }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <div class="text-gray-500 text-xs">还需减</div>
                                        <div class="font-bold text-orange-600">{{ $prediction['weight_remaining'] ?? '-' }}斤</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <div class="text-gray-500 text-xs">预计还需</div>
                                        <div class="font-bold">{{ $prediction['days_to_goal'] ?? '-' }}天</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-blue-50 rounded-lg p-3 text-sm text-blue-700">
                            <p>{{ $prediction['message'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Plateau detection -->
            @if($plateau)
            <div class="px-4 mt-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <h3 class="font-medium text-yellow-800 mb-2">📊 平台期提醒</h3>
                    <p class="text-sm text-yellow-700 mb-3">{{ $plateau['message'] }}</p>
                    <div class="text-sm text-yellow-700">
                        <p class="font-medium mb-1">建议：</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($plateau['suggestions'] as $suggestion)
                                <li>{{ $suggestion }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>
    </div>
</body>
</html>
