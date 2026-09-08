<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>搜索食物 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        <!-- Header -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3">
                <form action="{{ route('foods.search') }}" method="GET" class="relative">
                    <input type="text" name="q" value="{{ $query }}" placeholder="搜索食物..."
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white"
                           autofocus>
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search History (stored in localStorage via Alpine.js) -->
        @if(empty($query))
            <div class="px-4 mt-4" x-data="searchHistory()">
                @if(histories.length > 0)
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-500">搜索历史</h3>
                        <button @click="clearHistory()" class="text-xs text-gray-400">清空</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="h in histories" :key="h">
                            <a :href="'{{ route("foods.search") }}?q=' + encodeURIComponent(h)"
                               class="px-3 py-1.5 bg-white border border-gray-200 rounded-full text-sm text-gray-700 hover:bg-gray-50"
                               x-text="h"></a>
                        </template>
                    </div>
                @endif
            </div>
        @endif

        <!-- Results -->
        <div class="px-4 mt-4">
            @if($query && $results->isEmpty())
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-2">未找到"{{ $query }}"</div>
                    <a href="{{ route('foods.create') }}?name={{ urlencode($query) }}" class="text-blue-500 text-sm">创建自定义食物</a>
                </div>
            @elseif($results->isNotEmpty())
                <div class="space-y-2">
                    @foreach($results as $food)
                        <a href="{{ route('foods.show', $food) }}"
                           class="block bg-white rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $food->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $food->category }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-blue-600">{{ $food->calories_per_100g }}</div>
                                    <div class="text-xs text-gray-400">kcal/100g</div>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-2 text-xs text-gray-500">
                                <span>蛋白质 {{ $food->protein_per_100g }}g</span>
                                <span>碳水 {{ $food->carbs_per_100g }}g</span>
                                <span>脂肪 {{ $food->fat_per_100g }}g</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        @if(!empty($query))
            <div class="px-4 mt-4">
                <a href="{{ route('foods.create') }}?name={{ urlencode($query) }}" class="block w-full py-3 bg-white border border-gray-200 text-gray-700 rounded-lg text-center text-sm hover:bg-gray-50">
                    + 创建自定义食物
                </a>
            </div>
        @endif
    </div>

    <script>
        function searchHistory() {
            const key = 'search_history_user_{{ auth()->id() }}';
            return {
                histories: JSON.parse(localStorage.getItem(key) || '[]').slice(0, 10),
                clearHistory() {
                    localStorage.removeItem(key);
                    this.histories = [];
                }
            }
        }
    </script>
    </div>
</body>
</html>
