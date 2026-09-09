<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>搜索食物 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Search header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center gap-2">
                <a href="{{ route('foods.index') }}" class="shrink-0 rounded-lg p-1.5 -ml-1 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="返回食物库">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <form action="{{ route('foods.search') }}" method="GET" class="relative flex-1">
                    <input type="search" name="q" value="{{ $query }}" placeholder="搜索食物..."
                           class="input-field pl-10"
                           autofocus>
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--calo-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>
            </div>
        </div>

        @include('partials.flash')

        <!-- Search history -->
        @if(empty($query))
            <div class="px-4 mt-5 md:px-8" x-data="searchHistory()" x-show="histories.length > 0" x-cloak>
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">搜索历史</h3>
                    <button type="button" @click="clearHistory()" class="text-xs text-[var(--calo-muted)] hover:text-[var(--calo-ink)]">清空</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="h in histories" :key="h">
                        <a :href="'{{ route('foods.search') }}?q=' + encodeURIComponent(h)"
                           class="rounded-full border border-[var(--calo-line)] bg-white dark:bg-white/[0.04] px-3 py-1.5 text-sm text-[var(--calo-ink)] hover:border-brand-300 dark:hover:border-brand-700 transition"
                           x-text="h"></a>
                    </template>
                </div>
            </div>
        @endif

        <!-- Results -->
        <div class="px-4 mt-4 md:px-8">
            @if($query && $results->isEmpty())
                <div class="card py-10 text-center">
                    <div class="font-medium">未找到「{{ $query }}」</div>
                    <p class="mt-1 text-sm text-[var(--calo-muted)]">可以创建自定义食物，方便下次快速使用</p>
                    <a href="{{ route('foods.create') }}?name={{ urlencode($query) }}" class="btn-primary mt-4 inline-flex px-5 py-2.5 text-sm">创建自定义食物</a>
                </div>
            @elseif($results->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach($results as $food)
                        <a href="{{ route('foods.show', $food) }}"
                           class="card block p-4 hover:shadow-lift transition-shadow">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold">{{ $food->name }}</div>
                                    <div class="mt-0.5 text-xs text-[var(--calo-muted)]">{{ $food->category }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-number text-lg font-bold text-brand-700 dark:text-brand-400">{{ $food->calories_per_100g }}</div>
                                    <div class="text-[11px] text-[var(--calo-muted)]">kcal/100g</div>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
                                <span class="rounded-md bg-rose-50 dark:bg-rose-900/20 px-2 py-1 text-rose-700 dark:text-rose-300">蛋白质 {{ $food->protein_per_100g }}g</span>
                                <span class="rounded-md bg-amber-50 dark:bg-amber-900/20 px-2 py-1 text-amber-700 dark:text-amber-300">碳水 {{ $food->carbs_per_100g }}g</span>
                                <span class="rounded-md bg-violet-50 dark:bg-violet-900/20 px-2 py-1 text-violet-700 dark:text-violet-300">脂肪 {{ $food->fat_per_100g }}g</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if(!empty($query))
            <div class="px-4 mt-4 md:px-8">
                <a href="{{ route('foods.create') }}?name={{ urlencode($query) }}" class="card block w-full py-3.5 text-center text-sm font-medium hover:shadow-lift transition-shadow">
                    + 创建自定义食物
                </a>
            </div>
        @endif

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
