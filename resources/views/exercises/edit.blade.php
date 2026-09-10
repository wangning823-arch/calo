<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>编辑运动记录 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">编辑运动记录</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <form action="{{ route('exercises.update', $record) }}" method="POST" class="px-4 mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="card p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">运动日期</label>
                        <input type="date" name="date" value="{{ old('date', $record->date->format('Y-m-d')) }}"
                               max="{{ now()->toDateString() }}"
                               class="input-field">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">时间</label>
                        <input type="time" name="recorded_time" value="{{ old('recorded_time', $record->recorded_at ? $record->recorded_at->format('H:i') : '12:00') }}"
                               class="input-field">
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <label class="mb-2.5 block text-sm font-semibold">运动项目</label>
                <select name="exercise_type_id" class="input-field">
                    @foreach($categories as $cat)
                        <optgroup label="{{ $cat }}">
                            @foreach($exerciseTypes->where('category', $cat) as $type)
                                <option value="{{ $type->id }}" {{ old('exercise_type_id', $record->exercise_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (MET {{ $type->met_value }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">运动时长（分钟）</label>
                <input type="number" name="duration_minutes" min="1" max="600"
                       value="{{ old('duration_minutes', $record->duration_minutes) }}"
                       class="input-field">
            </div>

            <div class="card p-4">
                <label class="mb-2.5 block text-sm font-semibold">运动强度</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['light' => '轻度', 'moderate' => '中度', 'heavy' => '高强度'] as $val => $label)
                        <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                               style="{{ old('intensity', $record->intensity) === $val ? 'border-color: var(--calo-brand); background: var(--calo-brand-soft)' : '' }}">
                            <input type="radio" name="intensity" value="{{ $val }}"
                                   {{ old('intensity', $record->intensity) === $val ? 'checked' : '' }} class="sr-only">
                            <div class="text-xs font-medium">{{ $label }}</div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">距离（km，可选）</label>
                <input type="number" name="distance_km" step="0.1" min="0" max="500"
                       value="{{ old('distance_km', $record->distance_km) }}"
                       class="input-field">
            </div>

            <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                保存修改
            </button>
        </form>

        <div class="px-4 mt-4">
            <form action="{{ route('exercises.destroy', $record) }}" method="POST" onsubmit="return confirm('确定要删除这条运动记录吗？')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                    删除记录
                </button>
            </form>
        </div>
    </div>
</body>
</html>
