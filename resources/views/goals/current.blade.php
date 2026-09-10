<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>当前目标 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
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
                        <div class="text-xs text-gray-500">预期缺口</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-blue-600">{{ $goal->daily_calorie_budget }}</div>
                        <div class="text-xs text-gray-500">目标摄入</div>
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
                    <span class="text-gray-600">每日预期缺口</span>
                    <span class="font-medium text-orange-500">-{{ $goal->target_deficit }} kcal/天</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">每日目标摄入</span>
                    <span class="font-medium text-blue-600">{{ $goal->daily_calorie_budget }} kcal/天</span>
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
</div>
</body>
</html>
