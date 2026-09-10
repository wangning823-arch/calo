<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>编辑体重记录 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('weights.trend') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">编辑体重记录</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <form action="{{ route('weights.update', $record) }}" method="POST" class="px-4 mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="card p-4">
                <label class="block text-sm font-medium mb-1">日期</label>
                <input type="date" name="date" value="{{ old('date', $record->date->format('Y-m-d')) }}"
                       max="{{ now()->toDateString() }}"
                       class="input-field text-sm">
            </div>

            <div class="card p-4">
                <label class="block text-sm font-medium mb-1">体重</label>
                @php
                    $displayWeight = number_format((float)$record->weight_kg, 1);
                @endphp
                <div class="flex items-center gap-2">
                    <input type="number" name="weight" step="0.1" min="10" max="150"
                           value="{{ old('weight', $displayWeight) }}"
                           class="input-field flex-1 text-sm">
                    <span class="text-sm text-[var(--calo-muted)]">kg</span>
                </div>
            </div>

            <div class="card p-4">
                <label class="block text-sm font-medium mb-1">体脂率 (%)</label>
                <input type="number" name="body_fat_percentage" step="0.1" min="1" max="60"
                       value="{{ old('body_fat_percentage', $record->body_fat_percentage) }}"
                       placeholder="可选"
                       class="input-field text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="card p-4">
                    <label class="block text-sm font-medium mb-1">腰围 (cm)</label>
                    <input type="number" name="waist_cm" step="0.1" min="30" max="200"
                           value="{{ old('waist_cm', $record->waist_cm) }}"
                           placeholder="可选"
                           class="input-field text-sm">
                </div>
                <div class="card p-4">
                    <label class="block text-sm font-medium mb-1">臀围 (cm)</label>
                    <input type="number" name="hip_cm" step="0.1" min="30" max="200"
                           value="{{ old('hip_cm', $record->hip_cm) }}"
                           placeholder="可选"
                           class="input-field text-sm">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                保存修改
            </button>
        </form>

        <div class="px-4 mt-4 mb-4">
            <form action="{{ route('weights.destroy', $record) }}" method="POST" onsubmit="return confirm('确定要删除这条体重记录吗？')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 text-sm font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                    删除记录
                </button>
            </form>
        </div>
    </div>
</body>
</html>
