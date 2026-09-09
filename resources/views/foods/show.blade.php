<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>{{ $food->name }} - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
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
</div>
</body>
</html>
