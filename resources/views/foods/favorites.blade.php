<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>收藏夹 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('foods.index') }}" class="rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="返回食物库">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-[15px] font-semibold">收藏夹</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @include('partials.flash')

        <!-- Favorites List -->
        <div class="px-4 mt-4 md:px-8">
            @if($favorites->isEmpty())
                <div class="card p-10 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 dark:bg-violet-900/25 text-violet-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div class="font-medium">暂无收藏</div>
                    <p class="mt-1 text-sm text-[var(--calo-muted)]">收藏常用食物，记录时更快找到</p>
                    <a href="{{ route('foods.search') }}" class="btn-primary mt-4 inline-flex px-5 py-2.5 text-sm">去搜索食物</a>
                </div>
            @else
                <div class="card divide-y divide-[var(--calo-line)] overflow-hidden">
                    @foreach($favorites as $food)
                        <div class="p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors flex items-center justify-between">
                            <a href="{{ route('foods.show', $food) }}" class="flex-1 min-w-0">
                                <div class="text-sm font-semibold">{{ $food->name }}</div>
                                <div class="mt-0.5 text-xs text-[var(--calo-muted)]">{{ $food->category }} · {{ $food->calories_per_100g }} kcal/100g</div>
                            </a>
                            <form action="{{ route('foods.favorite', $food) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-md p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="取消收藏">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>
</html>
