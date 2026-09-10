<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>饮食记录 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="返回首页">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-[15px] font-semibold">饮食记录</h1>
                <a href="{{ route('meals.create') }}" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">新增</a>
            </div>
        </div>

        @include('partials.flash')

        <!-- Date filter -->
        <div class="px-4 mt-4 md:px-8">
            <form method="GET" action="{{ route('meals.index') }}" class="card p-3">
                <div class="flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[160px] max-w-xs">
                        <label for="date" class="block text-[11px] text-[var(--calo-muted)] mb-1">日期</label>
                        <input type="date" id="date" name="date" value="{{ $date }}"
                               class="w-full rounded-lg border border-[var(--calo-line)] bg-white dark:bg-white/[0.04] dark:border-white/10 px-2.5 py-2 text-sm text-[var(--calo-ink)]">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary px-4 py-2 text-sm">查询</button>
                        <a href="{{ route('meals.index', ['date' => now()->toDateString()]) }}" class="rounded-lg border border-[var(--calo-line)] px-3 py-2 text-sm text-[var(--calo-muted)] hover:bg-black/[0.03] dark:hover:bg-white/[0.05]">今天</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-4 mt-4 md:px-8">
            @if($records->count() > 0)
                <div class="card divide-y divide-[var(--calo-line)] overflow-hidden">
                    @foreach($records as $record)
                        <div class="p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold truncate">{{ $record->food->name ?? '未知食物' }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $record->meal_type === 'breakfast' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' :
                                               ($record->meal_type === 'lunch' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' :
                                               ($record->meal_type === 'dinner' ? 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' :
                                               'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300')) }}">
                                            {{ $mealTypes[$record->meal_type] ?? $record->meal_type }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-xs text-[var(--calo-muted)]">
                                        {{ $record->date->format('m/d') }}
                                        @if($record->recorded_at)
                                            {{ $record->recorded_at->format('H:i') }}
                                        @endif
                                        · {{ $record->serving_grams }}g
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-number text-sm font-bold text-flame-600 dark:text-orange-300">{{ round($record->calculated_calories) }} <span class="text-[11px] font-normal">kcal</span></div>
                                    <div class="mt-1.5 flex justify-end gap-1">
                                        <a href="{{ route('meals.edit', $record) }}" class="rounded-md px-2 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">编辑</a>
                                        <form method="POST" action="{{ route('meals.destroy', $record) }}" onsubmit="return confirm('确定删除这条饮食记录？')">
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
                <div class="mt-4">{{ $records->links() }}</div>
            @else
                <div class="card p-10 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-flame-50 dark:bg-orange-900/25 text-flame-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6v12m4-10v12M8 8v8m8-6v10M4 10v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
                    </div>
                    <div class="font-medium">该日暂无饮食记录</div>
                    <p class="mt-1 text-sm text-[var(--calo-muted)]">记录每一餐，热量收支一目了然</p>
                    <a href="{{ route('meals.create') }}" class="btn-primary mt-4 inline-flex px-5 py-2.5 text-sm">去记录饮食</a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
