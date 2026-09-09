<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>我的自定义食物 - Calo</title>
    @include('partials.app-scripts')
</head>
<body >
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('foods.index') }}" class="text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">我的自定义食物</h1>
                <a href="{{ route('foods.create') }}" class="text-blue-500 text-sm font-medium">+ 新增</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Records -->
        <div class="px-4 mt-4">
            @if($foods->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($foods as $food)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium dark:text-white">{{ $food->name }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">{{ $food->category }}</span>
                                    </div>
                                    <div class="flex gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span>蛋白质 {{ $food->protein_per_100g }}g</span>
                                        <span>碳水 {{ $food->carbs_per_100g }}g</span>
                                        <span>脂肪 {{ $food->fat_per_100g }}g</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-orange-500">{{ $food->calories_per_100g }} kcal</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">每100g</div>
                                    <div class="flex gap-2 mt-1">
                                        <a href="{{ route('foods.edit', $food) }}" class="text-xs text-blue-500">编辑</a>
                                        <form method="POST" action="{{ route('foods.destroy', $food) }}" onsubmit="return confirm('确定删除「{{ $food->name }}」？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500">删除</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                    <div class="text-gray-400 mb-2">还没有自定义食物</div>
                    <p class="text-xs text-gray-400 mb-3">搜索食物时如果找不到，可以创建自定义食物</p>
                    <a href="{{ route('foods.create') }}" class="text-blue-500 text-sm">创建自定义食物</a>
                </div>
            @endif
        </div>

        </div>
</body>
</html>
