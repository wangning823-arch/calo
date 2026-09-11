<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>编辑记录 - Calo</title>
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
                <h1 class="text-[15px] font-semibold">编辑记录</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <div class="px-4 mt-4" x-data="{ mealType: '{{ old('meal_type', $meal->meal_type) }}' }">
            <form method="POST" action="{{ route('meals.update', $meal) }}">
                @csrf
                @method('PUT')

                <!-- Meal Type -->
                <div class="card p-4 mb-4">
                    <label class="mb-2.5 block text-sm font-semibold">餐次</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['breakfast' => '早餐', 'lunch' => '午餐', 'dinner' => '晚餐', 'snack' => '加餐'] as $type => $label)
                        <button type="button" @click="mealType='{{ $type }}'"
                            :class="mealType==='{{ $type }}' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'"
                            class="py-2.5 rounded-xl text-sm font-medium transition-colors">{{ $label }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="meal_type" :value="mealType" value="{{ old('meal_type', $meal->meal_type) }}">
                </div>

                <!-- Date & Time -->
                <div class="card p-4 mb-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">日期</label>
                            <input type="date" name="date" value="{{ old('date', $meal->date->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" class="input-field">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">时间</label>
                            <input type="time" name="recorded_time" value="{{ old('recorded_time', $meal->recorded_at ? $meal->recorded_at->format('H:i') : '12:00') }}" class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Food Info -->
                <div class="card p-4 mb-4">
                    <div class="mb-1 text-sm font-medium">食物</div>
                    <div class="text-lg font-semibold">{{ $meal->food->name ?? '未知食物' }}</div>
                    <div class="text-sm text-[var(--calo-muted)]">{{ $meal->food->calories_per_100g ?? 0 }} kcal/100g</div>
                    <input type="hidden" name="food_id" value="{{ old('food_id', $meal->food_id) }}">
                </div>

                <!-- Serving Size -->
                <div class="card p-4 mb-4">
                    <label class="mb-1.5 block text-sm font-medium">份量 (克)</label>
                    <input type="number" name="serving_grams" value="{{ old('serving_grams', $meal->serving_grams) }}" min="1" max="5000" step="1" class="input-field font-number">
                    @error('serving_grams')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="card p-4 mb-4">
                    <label class="mb-1.5 block text-sm font-medium">备注</label>
                    <input type="text" name="notes" value="{{ old('notes', $meal->notes) }}" class="input-field" placeholder="可选">
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                    保存修改
                </button>
            </form>
        </div>
    </div>
</body>
</html>
