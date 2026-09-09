<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>记录饮食 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('meals.index') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">记录饮食</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <div class="px-4 mt-4 space-y-4" x-data="mealForm()">
            <!-- Meal Type -->
            <div class="card p-4">
                <label class="mb-2.5 block text-sm font-semibold">餐次</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['breakfast' => '早餐', 'lunch' => '午餐', 'dinner' => '晚餐', 'snack' => '加餐'] as $type => $label)
                    <button type="button" @click="mealType='{{ $type }}'"
                        :class="mealType==='{{ $type }}' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'"
                        class="py-2.5 rounded-xl text-sm font-medium transition-colors">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <!-- Date & Time -->
            <div class="card p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">日期</label>
                        <input type="date" name="date" x-model="date" max="{{ $today }}" class="input-field">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">时间</label>
                        <input type="time" name="recorded_time" x-model="recordedTime" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Search Food -->
            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-semibold">搜索食物</label>
                <div class="relative">
                    <input type="search" x-model="searchQuery" @input.debounce.300ms="searchFoods()" placeholder="输入食物名称，如「鸡胸肉」" class="input-field pl-10">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--calo-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div x-show="searchResults.length > 0" class="mt-3 max-h-52 overflow-y-auto rounded-xl border border-[var(--calo-line)] divide-y divide-[var(--calo-line)]">
                    <template x-for="food in searchResults" :key="food.id">
                        <button type="button" @click="selectFood(food)" class="w-full text-left px-3 py-2.5 hover:bg-brand-50/60 dark:hover:bg-brand-900/20 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium" x-text="food.name"></div>
                                    <div class="text-xs text-[var(--calo-muted)]" x-text="food.category"></div>
                                </div>
                                <span class="font-number text-xs text-[var(--calo-muted)]" x-text="food.calories_per_100g + ' kcal/100g'"></span>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="searchQuery.length >= 1 && searchResults.length === 0 && !selectedFood" class="mt-3 rounded-xl border border-dashed border-[var(--calo-line)] py-5 text-center text-sm text-[var(--calo-muted)]">
                    <p class="mb-2">未找到「<span x-text="searchQuery"></span>」</p>
                    <a :href="'{{ route('foods.create') }}?name=' + encodeURIComponent(searchQuery)" class="font-medium text-brand-700 dark:text-brand-400 hover:underline">创建自定义食物 →</a>
                </div>

                <div x-show="selectedFood" x-cloak class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-brand-50 dark:bg-brand-900/25 p-3.5">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold truncate" x-text="selectedFood?.name"></div>
                        <div class="text-xs text-brand-700/80 dark:text-brand-300/80" x-text="selectedFood?.calories_per_100g + ' kcal/100g'"></div>
                    </div>
                    <button type="button" @click="selectedFood=null; searchQuery=''" class="shrink-0 rounded-lg p-1.5 text-[var(--calo-muted)] hover:bg-black/[0.05] dark:hover:bg-white/[0.08]" aria-label="取消选择">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Serving size -->
            <div x-show="selectedFood" x-cloak class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-semibold">份量</label>
                    <span class="font-number text-sm text-brand-700 dark:text-brand-400" x-text="estimatedCalories + ' kcal'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="servingGrams = Math.max(1, servingGrams - 10)" class="h-11 w-11 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-lg font-semibold">−</button>
                    <div class="flex-1">
                        <input type="number" name="serving_grams" x-model.number="servingGrams" min="1" class="input-field text-center font-number" style="margin:0;">
                        <p class="mt-1 text-center text-[11px] text-[var(--calo-muted)]">克 (g)</p>
                    </div>
                    <button type="button" @click="servingGrams = servingGrams + 10" class="h-11 w-11 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-lg font-semibold">+</button>
                </div>
                <div class="mt-3 grid grid-cols-4 gap-2">
                    <template x-for="g in [50, 100, 150, 200]" :key="g">
                        <button type="button" @click="servingGrams = g" :class="servingGrams === g ? 'bg-brand-600 text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'" class="py-2 rounded-lg text-xs font-medium transition" x-text="g + 'g'"></button>
                    </template>
                </div>
            </div>

            <form method="POST" action="{{ route('meals.store') }}" class="pt-1">
                @csrf
                <input type="hidden" name="meal_type" :value="mealType">
                <input type="hidden" name="food_id" :value="selectedFood?.id">
                <input type="hidden" name="serving_grams" :value="servingGrams">
                <input type="hidden" name="date" :value="date">
                <input type="hidden" name="recorded_time" :value="recordedTime">
                <button type="submit" :disabled="!selectedFood" class="btn-primary w-full py-3.5 text-[15px]">
                    保存记录
                </button>
            </form>
        </div>

        <script>
            function mealForm() {
                return {
                    mealType: '{{ $mealType }}',
                    date: '{{ $today }}',
                    recordedTime: '{{ $now }}',
                    searchQuery: '',
                    searchResults: [],
                    selectedFood: null,
                    servingGrams: 100,
                    get estimatedCalories() {
                        if (!this.selectedFood) return 0;
                        return Math.round((this.selectedFood.calories_per_100g * this.servingGrams) / 100);
                    },
                    async searchFoods() {
                        if (this.searchQuery.length < 1) {
                            this.searchResults = [];
                            return;
                        }
                        try {
                            const res = await fetch('{{ route("api.foods.search") }}?q=' + encodeURIComponent(this.searchQuery), {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            this.searchResults = data.data || data || [];
                        } catch (e) {
                            this.searchResults = [];
                        }
                    },
                    selectFood(food) {
                        this.selectedFood = food;
                        this.searchResults = [];
                        this.searchQuery = food.name;
                    }
                }
            }
        </script>
    </div>
</body>
</html>
