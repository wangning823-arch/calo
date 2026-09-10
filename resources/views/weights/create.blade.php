<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>记录体重 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">记录体重</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <!-- Latest weight display -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h2 class="text-sm font-medium text-[var(--calo-muted)] mb-2">上次记录</h2>
                @if($latestWeight)
                    <div class="text-3xl font-bold text-brand-700 dark:text-brand-400">
                        {{ $latestWeight['weight_display'] }}
                        <span class="text-base font-normal text-[var(--calo-muted)]">kg</span>
                    </div>
                    <div class="text-xs text-[var(--calo-muted)] mt-1">{{ $latestWeight['date'] }}</div>
                @else
                    <div class="text-[var(--calo-muted)]">暂无记录</div>
                @endif
            </div>
        </div>

        <!-- Weight form -->
        <form action="{{ route('weights.store') }}" method="POST" class="px-4 mt-4 space-y-4"
              x-data="{ weight: '{{ old('weight', $latestWeight['weight_display'] ?? '') }}' }">
            @csrf
            <input type="hidden" name="unit" value="kg">

            <!-- Date -->
            <div class="card p-4">
                <label class="block text-sm font-medium mb-1">日期</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                       max="{{ now()->toDateString() }}"
                       min="{{ now()->subDays(30)->toDateString() }}"
                       class="input-field text-sm">
            </div>

            <!-- Weight -->
            <div class="card p-4">
                <label class="block text-sm font-medium mb-2">体重</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="weight" step="0.1" min="10" max="150"
                           x-model="weight"
                           value="{{ old('weight', $latestWeight['weight_display'] ?? '') }}"
                           placeholder="例：65"
                           class="input-field flex-1 text-sm">
                    <span class="text-sm text-[var(--calo-muted)]">kg</span>
                </div>
                <p class="text-xs text-[var(--calo-muted)] mt-1">范围：10-150kg</p>
            </div>

            <!-- Optional measurements -->
            <div class="card p-4">
                <h3 class="text-sm font-medium mb-3">可选测量数据</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-[var(--calo-muted)] mb-1">体脂率 (%)</label>
                        <input type="number" name="body_fat_percentage" step="0.1" min="1" max="60"
                               value="{{ old('body_fat_percentage') }}"
                               placeholder="可选"
                               class="input-field text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-[var(--calo-muted)] mb-1">腰围 (cm)</label>
                            <input type="number" name="waist_cm" step="0.1" min="30" max="200"
                                   value="{{ old('waist_cm') }}"
                                   placeholder="可选"
                                   class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-[var(--calo-muted)] mb-1">臀围 (cm)</label>
                            <input type="number" name="hip_cm" step="0.1" min="30" max="200"
                                   value="{{ old('hip_cm') }}"
                                   placeholder="可选"
                                   class="input-field text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="rounded-xl bg-brand-50 dark:bg-brand-900/25 p-3 text-xs text-brand-800 dark:text-brand-200 shadow-soft">
                单日波动±1kg属正常，请关注7日均线趋势。
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                保存记录
            </button>
        </form>

        <!-- Recent records -->
        @if($recentRecords->count() > 0)
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h3 class="text-sm font-medium mb-3">最近记录</h3>
                <div class="divide-y divide-[var(--calo-line)]">
                    @foreach($recentRecords->take(10) as $record)
                        <div class="flex items-center justify-between py-2 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <span class="text-sm text-[var(--calo-muted)]">{{ $record->date->format('m/d') }}</span>
                            <span class="text-sm font-semibold">
                                {{ number_format((float)$record->weight_kg, 1) . ' kg' }}
                            </span>
                            <div class="flex gap-2">
                                <a href="{{ route('weights.edit', $record) }}" class="rounded-md px-2 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/25">编辑</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
