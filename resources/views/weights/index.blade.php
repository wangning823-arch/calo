<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>体重记录 - Calo</title>
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
                <h1 class="text-[15px] font-semibold">体重记录</h1>
                <a href="{{ route('weights.create') }}" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">新增</a>
            </div>
        </div>

        @include('partials.flash')

        <!-- Quick Actions -->
        <div class="px-4 mt-4 flex gap-2">
            <a href="{{ route('weights.trend') }}" class="card flex-1 p-3 text-center text-sm font-medium text-brand-700 dark:text-brand-400 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                📈 查看趋势
            </a>
        </div>

        <!-- Date filter -->
        <div class="px-4 mt-4">
            <form method="GET" action="{{ route('weights.index') }}" class="card p-3">
                <div class="flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[160px] max-w-xs">
                        <label for="date" class="block text-[11px] text-[var(--calo-muted)] mb-1">日期</label>
                        <input type="date" id="date" name="date" value="{{ $date }}"
                               class="w-full rounded-lg border border-[var(--calo-line)] bg-white dark:bg-white/[0.04] px-2.5 py-2 text-sm text-[var(--calo-ink)]">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary px-4 py-2 text-sm">查询</button>
                        <a href="{{ route('weights.index', ['date' => now()->toDateString()]) }}" class="rounded-lg border border-[var(--calo-line)] px-3 py-2 text-sm text-[var(--calo-muted)] hover:bg-black/[0.03] dark:hover:bg-white/[0.05]">今天</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Records -->
        <div class="px-4 mt-4">
            @if($records->count() > 0)
                <div class="card divide-y divide-[var(--calo-line)] overflow-hidden">
                    @foreach($records as $record)
                        <div class="p-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold">
                                            {{ number_format((float)$record->weight_kg, 1) . ' kg' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-[var(--calo-muted)] mt-1">
                                        {{ $record->date->format('Y年m月d日') }}
                                        @if($record->body_fat_percentage)
                                            · 体脂 {{ $record->body_fat_percentage }}%
                                        @endif
                                        @if($record->waist_cm)
                                            · 腰围 {{ $record->waist_cm }}cm
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex gap-2">
                                        <a href="{{ route('weights.edit', $record) }}" class="rounded-md px-2 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">编辑</a>
                                        <form method="POST" action="{{ route('weights.destroy', $record) }}" onsubmit="return confirm('确定删除这条体重记录？')">
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
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 dark:bg-brand-900/25 text-brand-600 dark:text-brand-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 12h4l3-9 4 18 3-9h4"/></svg>
                    </div>
                    <div class="font-medium">该日暂无体重记录</div>
                    <p class="mt-1 text-sm text-[var(--calo-muted)]">记录体重变化，掌握身体趋势</p>
                    <a href="{{ route('weights.create') }}" class="btn-primary mt-4 inline-flex px-5 py-2.5 text-sm">去记录体重</a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
