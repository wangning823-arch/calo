<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>食物库 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="w-12"></div>
                <h1 class="text-[15px] font-semibold">食物库</h1>
                <div class="flex items-center gap-1">
                    <a href="{{ route('foods.manage') }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">我的</a>
                    <a href="{{ route('foods.search') }}" class="rounded-lg p-2 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="搜索食物">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        @include('partials.flash')

        <!-- Categories -->
        <div class="px-4 mt-4 md:px-8">
            <div class="grid grid-cols-2 gap-3">
                @foreach($categories as $cat => $count)
                    <a href="{{ route('foods.index', ['category' => $cat]) }}"
                       class="card p-4 hover:shadow-lift transition-shadow {{ $category === $cat ? 'ring-2 ring-brand-500 border-brand-300 dark:border-brand-700' : '' }}">
                        <div class="text-sm font-semibold">{{ $cat }}</div>
                        <div class="mt-1 text-xs text-[var(--calo-muted)]">{{ $count }} 种食物</div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Foods in Category -->
        @if($foods->isNotEmpty())
            <div class="px-4 mt-5 md:px-8">
                <h2 class="mb-2 text-sm font-semibold text-[var(--calo-muted)]">{{ $category }}</h2>
                <div class="card divide-y divide-[var(--calo-line)] overflow-hidden">
                    @foreach($foods as $food)
                        <a href="{{ route('foods.show', $food) }}"
                           class="flex items-center justify-between p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <div class="text-sm font-medium">{{ $food->name }}</div>
                            <div class="font-number text-sm font-bold text-brand-700 dark:text-brand-400">{{ $food->calories_per_100g }} <span class="text-[11px] font-normal text-[var(--calo-muted)]">kcal</span></div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>
