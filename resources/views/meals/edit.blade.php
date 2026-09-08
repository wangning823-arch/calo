<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>编辑记录 - Calo</title>
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
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">编辑记录</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="px-4 mt-4" x-data="{ mealType: '{{ $meal->meal_type }}' }">
            <form method="POST" action="{{ route('meals.update', $meal) }}">
                @csrf
                @method('PUT')

                <!-- Meal Type -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">餐次</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" @click="mealType='breakfast'" :class="mealType==='breakfast' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">早餐</button>
                        <button type="button" @click="mealType='lunch'" :class="mealType==='lunch' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">午餐</button>
                        <button type="button" @click="mealType='dinner'" :class="mealType==='dinner' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">晚餐</button>
                        <button type="button" @click="mealType='snack'" :class="mealType==='snack' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="py-2 rounded-lg text-sm font-medium transition-colors">加餐</button>
                    </div>
                    <input type="hidden" name="meal_type" :value="mealType">
                </div>

                <!-- Food Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">食物</div>
                    <div class="text-lg font-semibold dark:text-white">{{ $meal->food->name ?? '未知食物' }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $meal->food->calories_per_100g ?? 0 }} kcal/100g</div>
                    <input type="hidden" name="food_id" value="{{ $meal->food_id }}">
                </div>

                <!-- Serving Size -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">份量 (克)</label>
                    <input type="number" name="serving_grams" value="{{ $meal->serving_grams }}" min="1" max="5000" step="10" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">备注</label>
                    <input type="text" name="notes" value="{{ $meal->notes }}" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white" placeholder="可选">
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    保存修改
                </button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
