<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>我的自定义食物 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
            .desktop-sidebar { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    @include('partials.sidebar')
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
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

        <!-- Mobile Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('foods.search') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="text-xs mt-0.5">搜索</span>
                </a>
                <a href="{{ route('foods.favorites') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="text-xs mt-0.5">收藏</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
