<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $food->name }} - Calo</title>
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
                <a href="javascript:history.back()" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">食物详情</h1>
                <form action="{{ route('foods.favorite', $food) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-{{ $isFavorite ? 'red' : 'gray' }}-500">
                        <svg class="w-6 h-6" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Food Info -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $food->name }}</h2>
                        <div class="text-sm text-gray-500 mt-1">{{ $food->category }}</div>
                        @if($food->aliases)
                            <div class="text-xs text-gray-400 mt-1">
                                别名: {{ is_array(json_decode($food->aliases)) ? implode(', ', json_decode($food->aliases)) : $food->aliases }}
                            </div>
                        @endif
                    </div>
                    @if($food->is_user_custom)
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">自定义</span>
                    @endif
                </div>

                <!-- Nutrition per 100g -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">每100g营养成分</h3>
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div>
                            <div class="text-xl font-bold text-orange-500">{{ $food->calories_per_100g }}</div>
                            <div class="text-xs text-gray-500">热量(kcal)</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-red-500">{{ $food->protein_per_100g }}</div>
                            <div class="text-xs text-gray-500">蛋白质(g)</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-yellow-500">{{ $food->carbs_per_100g }}</div>
                            <div class="text-xs text-gray-500">碳水(g)</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-blue-500">{{ $food->fat_per_100g }}</div>
                            <div class="text-xs text-gray-500">脂肪(g)</div>
                        </div>
                    </div>
                </div>

                <!-- Serving Info -->
                <div class="mt-4 text-sm text-gray-600">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span>标准份量</span>
                        <span class="font-medium">{{ $food->serving_size }}{{ $food->serving_unit }}</span>
                    </div>
                    @if($food->source)
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span>数据来源</span>
                            <span class="font-medium">{{ $food->source }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Macros Ratio -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">营养素比例</h3>
                @php
                    $total = $food->protein_per_100g * 4 + $food->carbs_per_100g * 4 + $food->fat_per_100g * 9;
                    $proteinPct = $total > 0 ? round(($food->protein_per_100g * 4 / $total) * 100) : 0;
                    $carbsPct = $total > 0 ? round(($food->carbs_per_100g * 4 / $total) * 100) : 0;
                    $fatPct = $total > 0 ? round(($food->fat_per_100g * 9 / $total) * 100) : 0;
                @endphp
                <div class="flex h-4 rounded-full overflow-hidden">
                    <div class="bg-red-400" style="width: {{ $proteinPct }}%"></div>
                    <div class="bg-yellow-400" style="width: {{ $carbsPct }}%"></div>
                    <div class="bg-blue-400" style="width: {{ $fatPct }}%"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    <span>蛋白质 {{ $proteinPct }}%</span>
                    <span>碳水 {{ $carbsPct }}%</span>
                    <span>脂肪 {{ $fatPct }}%</span>
                </div>
            </div>
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
                <a href="{{ route('foods.search') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
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
