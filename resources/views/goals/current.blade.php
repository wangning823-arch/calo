<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>当前目标 - Calo</title>
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
        <!-- Header -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">当前目标</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Progress Card -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-medium text-gray-500">减重进度</h2>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">进行中</span>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">已完成</span>
                        <span class="font-medium text-blue-600">{{ $progress ? number_format($progress, 0) . '%' : '0%' }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progress ?: 0 }}%"></div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-3 text-center">
                    <div>
                        <div class="text-xl font-bold text-gray-800">{{ $currentWeight ? number_format($currentWeight, 1) : '-' }}</div>
                        <div class="text-xs text-gray-500">当前(kg)</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-green-600">{{ $goal->target_weight }}</div>
                        <div class="text-xs text-gray-500">目标(kg)</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-orange-500">-{{ $goal->target_deficit }}</div>
                        <div class="text-xs text-gray-500">缺口(kcal)</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-blue-600">{{ $goal->daily_calorie_budget }}</div>
                        <div class="text-xs text-gray-500">预算(kcal)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goal Details -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                <h2 class="text-sm font-medium text-gray-500">目标详情</h2>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">起始体重</span>
                    <span class="font-medium">{{ $goal->start_weight }} kg</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">目标体重</span>
                    <span class="font-medium">{{ $goal->target_weight }} kg</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">需减重</span>
                    <span class="font-medium text-red-500">{{ number_format($goal->start_weight - $goal->target_weight, 1) }} kg</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">目标日期</span>
                    <span class="font-medium">{{ $goal->target_date->format('Y年m月d日') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">每日热量缺口</span>
                    <span class="font-medium text-orange-500">-{{ $goal->target_deficit }} kcal/天</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">每周减重速率</span>
                    <span class="font-medium">{{ number_format(($goal->start_weight - $goal->target_weight) / max(1, $goal->created_at->diffInWeeks($goal->target_date)), 1) }} kg/周</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">设定日期</span>
                    <span class="font-medium">{{ $goal->created_at->format('Y年m月d日') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-4 mt-4 space-y-3">
            <a href="{{ route('goals.create') }}" class="block w-full py-3 bg-white border border-gray-200 text-gray-700 rounded-lg text-center font-medium hover:bg-gray-50 transition-colors">
                修改目标
            </a>
        </div>

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('meals.create') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-xs mt-0.5">记录</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-0.5">我的</span>
                </a>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
