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
                <a href="{{ route('foods.index') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">我的自定义食物</h1>
                <a href="{{ route('foods.create') }}" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">新增</a>
            </div>
        </div>

        @include('partials.flash')

        <!-- Records -->
        <div class="px-4 mt-4">
            @if($foods->count() > 0)
                <div class="card divide-y divide-[var(--calo-line)] overflow-hidden">
                    @foreach($foods as $food)
                        <div class="p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold truncate">{{ $food->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]">{{ $food->category }}</span>
                                    </div>
                                    <div class="mt-1 flex gap-3 text-xs text-[var(--calo-muted)]">
                                        <span>蛋白质 {{ $food->protein_per_100g }}g</span>
                                        <span>碳水 {{ $food->carbs_per_100g }}g</span>
                                        <span>脂肪 {{ $food->fat_per_100g }}g</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-number text-sm font-bold text-brand-700 dark:text-brand-400">{{ $food->calories_per_100g }} <span class="text-[11px] font-normal text-[var(--calo-muted)]">kcal</span></div>
                                    <div class="text-xs text-[var(--calo-muted)]">每100g</div>
                                    <div class="mt-1.5 flex justify-end gap-1">
                                        <a href="{{ route('foods.edit', $food) }}" class="rounded-md px-2 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">编辑</a>
                                        <form method="POST" action="{{ route('foods.destroy', $food) }}" onsubmit="return confirm('确定删除「{{ $food->name }}」？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/25">删除</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-10 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 dark:bg-brand-900/25 text-brand-600 dark:text-brand-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6v12m4-10v12M8 8v8m8-6v10M4 10v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
                    </div>
                    <div class="font-medium">还没有自定义食物</div>
                    <p class="mt-1 text-sm text-[var(--calo-muted)]">搜索食物时如果找不到，可以创建自定义食物</p>
                    <a href="{{ route('foods.create') }}" class="btn-primary mt-4 inline-flex px-5 py-2.5 text-sm">创建自定义食物</a>
                </div>
            @endif
        </div>

        </div>
</body>
</html>
