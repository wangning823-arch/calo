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
                <a href="javascript:history.back()" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">食物详情</h1>
                <form action="{{ route('foods.favorite', $food) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg p-1.5 -mr-1.5 {{ $isFavorite ? 'text-red-500' : 'text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <svg class="w-6 h-6" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        @include('partials.flash')

        <!-- Food Info -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold">{{ $food->name }}</h2>
                        <div class="mt-1 text-sm text-[var(--calo-muted)]">{{ $food->category }}</div>
                        @if($food->aliases)
                            <div class="mt-1 text-xs text-[var(--calo-muted)]">
                                别名: {{ is_array(json_decode($food->aliases)) ? implode(', ', json_decode($food->aliases)) : $food->aliases }}
                            </div>
                        @endif
                    </div>
                    @if($food->is_user_custom)
                        <span class="shrink-0 rounded-full bg-brand-50 dark:bg-brand-900/25 px-2.5 py-1 text-xs font-medium text-brand-700 dark:text-brand-300">自定义</span>
                    @endif
                </div>

                <!-- Nutrition per 100g -->
                <div class="rounded-xl bg-black/[0.03] dark:bg-white/[0.04] p-4">
                    <h3 class="mb-3 text-sm font-semibold">每100g营养成分</h3>
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div>
                            <div class="font-number text-xl font-bold text-flame-600 dark:text-orange-300">{{ $food->calories_per_100g }}</div>
                            <div class="text-xs text-[var(--calo-muted)]">热量(kcal)</div>
                        </div>
                        <div>
                            <div class="font-number text-xl font-bold text-rose-600 dark:text-rose-400">{{ $food->protein_per_100g }}</div>
                            <div class="text-xs text-[var(--calo-muted)]">蛋白质(g)</div>
                        </div>
                        <div>
                            <div class="font-number text-xl font-bold text-amber-600 dark:text-amber-400">{{ $food->carbs_per_100g }}</div>
                            <div class="text-xs text-[var(--calo-muted)]">碳水(g)</div>
                        </div>
                        <div>
                            <div class="font-number text-xl font-bold text-brand-600 dark:text-brand-400">{{ $food->fat_per_100g }}</div>
                            <div class="text-xs text-[var(--calo-muted)]">脂肪(g)</div>
                        </div>
                    </div>
                </div>

                <!-- Serving Info -->
                <div class="mt-4 text-sm">
                    <div class="flex justify-between py-2 border-b border-[var(--calo-line)]">
                        <span>标准份量</span>
                        <span class="font-medium">{{ $food->serving_size }}{{ $food->serving_unit }}</span>
                    </div>
                    @if($food->source)
                        <div class="flex justify-between py-2 border-b border-[var(--calo-line)]">
                            <span>数据来源</span>
                            <span class="font-medium">{{ $food->source }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Macros Ratio -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h3 class="mb-3 text-sm font-semibold">营养素比例</h3>
                @php
                    $total = $food->protein_per_100g * 4 + $food->carbs_per_100g * 4 + $food->fat_per_100g * 9;
                    $proteinPct = $total > 0 ? round(($food->protein_per_100g * 4 / $total) * 100) : 0;
                    $carbsPct = $total > 0 ? round(($food->carbs_per_100g * 4 / $total) * 100) : 0;
                    $fatPct = $total > 0 ? round(($food->fat_per_100g * 9 / $total) * 100) : 0;
                @endphp
                <div class="flex h-4 rounded-full overflow-hidden">
                    <div class="bg-rose-400" style="width: {{ $proteinPct }}%"></div>
                    <div class="bg-amber-400" style="width: {{ $carbsPct }}%"></div>
                    <div class="bg-brand-400" style="width: {{ $fatPct }}%"></div>
                </div>
                <div class="mt-2 flex justify-between text-xs text-[var(--calo-muted)]">
                    <span>蛋白质 {{ $proteinPct }}%</span>
                    <span>碳水 {{ $carbsPct }}%</span>
                    <span>脂肪 {{ $fatPct }}%</span>
                </div>
            </div>
        </div>
</div>
</body>
</html>
