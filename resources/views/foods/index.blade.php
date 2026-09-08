<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>食物库 - Calo</title>
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
                <div class="w-6"></div>
                <h1 class="text-lg font-semibold">食物库</h1>
                <div class="flex items-center gap-3">
                    <a href="{{ route('foods.manage') }}" class="text-xs text-blue-500 font-medium">我的</a>
                    <a href="{{ route('foods.search') }}" class="text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="px-4 mt-4">
            <div class="grid grid-cols-2 gap-3">
                @foreach($categories as $cat => $count)
                    <a href="{{ route('foods.index', ['category' => $cat]) }}"
                       class="bg-white rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow {{ $category === $cat ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="text-sm font-medium text-gray-800">{{ $cat }}</div>
                        <div class="text-xs text-gray-500">{{ $count }} 种食物</div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Foods in Category -->
        @if($foods->isNotEmpty())
            <div class="px-4 mt-4">
                <h2 class="text-sm font-medium text-gray-500 mb-2">{{ $category }}</h2>
                <div class="space-y-2">
                    @foreach($foods as $food)
                        <a href="{{ route('foods.show', $food) }}"
                           class="block bg-white rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $food->name }}</div>
                                </div>
                                <div class="text-sm font-bold text-blue-600">{{ $food->calories_per_100g }} kcal</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('foods.search') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="text-xs mt-0.5">搜索</span>
                </a>
                <a href="{{ route('foods.favorites') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="text-xs mt-0.5">收藏</span>
                </a>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
