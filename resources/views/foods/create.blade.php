<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>创建自定义食物 - Calo</title>
    @include('partials.app-scripts')
</head>
<body >
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">创建自定义食物</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <form action="{{ route('foods.store') }}" method="POST" class="px-4 mt-4 space-y-4"
              x-data="{ unit: 'kcal', calories: '' }">
            @csrf

            <!-- Food Name -->
            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">食物名称</label>
                <input type="text" name="name" value="{{ old('name', request('name')) }}" required maxlength="255"
                       class="input-field"
                       placeholder="例如：红烧肉、全麦面包">
            </div>

            <!-- Category -->
            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">分类</label>
                <select name="category" class="input-field">
                    @foreach(['主食', '蔬菜', '水果', '肉类', '蛋奶', '零食', '饮料', '调味品', '其他'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Calories with unit toggle -->
            <div class="card p-4">
                <div class="mb-3 flex items-center justify-between">
                    <label class="block text-sm font-medium">热量 (每100g)</label>
                    <div class="flex gap-1 text-xs">
                        <button type="button" @click="unit = 'kcal'"
                                :class="unit === 'kcal' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'"
                                class="px-2.5 py-1 rounded-lg font-medium transition">kcal</button>
                        <button type="button" @click="unit = 'kj'"
                                :class="unit === 'kj' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'"
                                class="px-2.5 py-1 rounded-lg font-medium transition">kJ</button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="calories_per_100g" step="0.1" min="0" max="5000"
                           x-model="calories" value="{{ old('calories_per_100g') }}" required
                           class="input-field flex-1 font-number"
                           :placeholder="unit === 'kcal' ? '例如：150' : '例如：628'">
                    <span class="text-sm text-[var(--calo-muted)] whitespace-nowrap" x-text="unit === 'kcal' ? 'kcal' : 'kJ'"></span>
                </div>
                <input type="hidden" name="calorie_unit" :value="unit">
                <p class="text-xs text-[var(--calo-muted)] mt-2">
                    <span x-show="unit === 'kcal'">1 kcal ≈ 4.184 kJ</span>
                    <span x-show="unit === 'kj'">1 kJ ≈ 0.239 kcal</span>
                </p>
            </div>

            <!-- Optional nutrition -->
            <div class="card p-4">
                <h3 class="mb-3 text-sm font-semibold">营养素 (每100g，可选)</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="mb-1 block text-xs text-[var(--calo-muted)]">蛋白质 (g)</label>
                        <input type="number" name="protein_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('protein_per_100g') }}"
                               class="input-field font-number">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-[var(--calo-muted)]">碳水 (g)</label>
                        <input type="number" name="carbs_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('carbs_per_100g') }}"
                               class="input-field font-number">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-[var(--calo-muted)]">脂肪 (g)</label>
                        <input type="number" name="fat_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('fat_per_100g') }}"
                               class="input-field font-number">
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div x-show="calories" x-cloak class="rounded-xl bg-brand-50 dark:bg-brand-900/25 p-3.5 text-sm text-brand-700 dark:text-brand-300">
                <span class="font-medium">预览：</span>
                <span x-text="unit === 'kcal' ? calories + ' kcal/100g' : (calories / 4.184).toFixed(1) + ' kcal/100g'"></span>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                保存食物
            </button>
        </form>
    </div>
</body>
</html>
