<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>食谱库 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">食谱库</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            @if(empty($recipes))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                <div class="text-gray-400 dark:text-gray-500 mb-2">暂无食谱</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">管理员正在添加更多食谱</div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($recipes as $recipe)
                <a href="{{ route('content.recipe', $recipe['id']) }}" class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ $recipe['title'] }}</h3>
                            <div class="flex gap-2 mt-1">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $recipe['meal_type'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-orange-600">{{ $recipe['total_calories'] }}</div>
                            <div class="text-xs text-gray-400">kcal</div>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>蛋白质 {{ $recipe['protein'] }}g</span>
                        <span>碳水 {{ $recipe['carbs'] }}g</span>
                        <span>脂肪 {{ $recipe['fat'] }}g</span>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            <!-- Disclaimer -->
            <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400 mt-4">
                <p>本食谱为一般健康信息，非医疗建议。如有特殊健康状况，请咨询医生。</p>
            </div>
        </div>
    </div>
</body>
</html>
