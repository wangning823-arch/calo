<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>记录饮食 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
            .desktop-sidebar { display: none !important; }
        }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen pb-20">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">记录饮食</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="px-4 mt-4" x-data="mealForm()">
            <!-- Meal Type -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">餐次</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" @click="mealType='breakfast'" :class="mealType==='breakfast' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">早餐</button>
                    <button type="button" @click="mealType='lunch'" :class="mealType==='lunch' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">午餐</button>
                    <button type="button" @click="mealType='dinner'" :class="mealType==='dinner' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">晚餐</button>
                    <button type="button" @click="mealType='snack'" :class="mealType==='snack' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">加餐</button>
                </div>
            </div>

            <!-- Date -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">日期</label>
                <input type="date" name="date" x-model="date" max="{{ $today }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Search Food -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">搜索食物</label>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchFoods()" placeholder="输入食物名称..." class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white">
                    <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Search Results -->
                <div x-show="searchResults.length > 0" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                    <template x-for="food in searchResults" :key="food.id">
                        <div @click="selectFood(food)" class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-0">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium dark:text-white" x-text="food.name"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="food.calories_per_100g + ' kcal/100g'"></span>
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500" x-text="food.category"></div>
                        </div>
                    </template>
                </div>

                <!-- Selected Food -->
                <div x-show="selectedFood" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm font-medium text-blue-800 dark:text-blue-300" x-text="selectedFood?.name"></div>
                            <div class="text-xs text-blue-600 dark:text-blue-400" x-text="selectedFood?.calories_per_100g + ' kcal/100g'"></div>
                        </div>
                        <button type="button" @click="selectedFood=null; searchQuery=''" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Serving Size -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">份量 (克)</label>
                <input type="number" name="serving_grams" x-model="servingGrams" min="1" max="5000" step="10" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white" placeholder="100">
                <div class="mt-2 flex gap-2">
                    <button type="button" @click="servingGrams=50" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">50g</button>
                    <button type="button" @click="servingGrams=100" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">100g</button>
                    <button type="button" @click="servingGrams=200" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">200g</button>
                    <button type="button" @click="servingGrams=300" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">300g</button>
                </div>
            </div>

            <!-- Calories Preview -->
            <div x-show="selectedFood && servingGrams" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">预计热量</div>
                <div class="text-2xl font-bold text-blue-600">
                    <span x-text="Math.round(selectedFood?.calories_per_100g * servingGrams / 100)"></span> kcal
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">备注（可选）</label>
                <input type="text" name="notes" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white" placeholder="如：食堂午餐">
            </div>

            <!-- Popular Foods Quick Add -->
            @if($popularFoods->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">常用食物</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($popularFoods as $food)
                    <button type="button" @click='selectFood(@json($food))' class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-full text-xs text-gray-700 dark:text-gray-300 transition-colors">
                        {{ $food->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Submit -->
            <form method="POST" action="{{ route('meals.store') }}" x-ref="form">
                @csrf
                <input type="hidden" name="meal_type" :value="mealType">
                <input type="hidden" name="food_id" :value="selectedFood?.id">
                <input type="hidden" name="serving_grams" :value="servingGrams">
                <input type="hidden" name="date" :value="date">
                <button type="submit" :disabled="!selectedFood" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    保存记录
                </button>
            </form>
        </div>

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('meals.create') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-xs mt-0.5">记录</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-0.5">我的</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function mealForm() {
            return {
                mealType: '{{ $mealType }}',
                date: '{{ $today }}',
                searchQuery: '',
                searchResults: [],
                selectedFood: null,
                servingGrams: 100,
                async searchFoods() {
                    if (this.searchQuery.length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch('{{ route("api.foods.search") }}?q=' + encodeURIComponent(this.searchQuery));
                    const data = await res.json();
                    this.searchResults = data.data || [];
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
