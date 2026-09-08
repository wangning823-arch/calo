<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>编辑食物 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; flex-direction: column; }
        }
        @media (max-width: 767px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    @include('partials.sidebar')
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('foods.manage') }}" class="text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">编辑食物</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('foods.update', $food) }}" method="POST" class="px-4 mt-4 space-y-4"
              x-data="{ unit: 'kcal', calories: '{{ $food->calories_per_100g }}' }">
            @csrf
            @method('PUT')

            <!-- Food Name -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">食物名称</label>
                <input type="text" name="name" value="{{ old('name', $food->name) }}" required maxlength="255"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Category -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">分类</label>
                <select name="category"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach(['主食', '蔬菜', '水果', '肉类', '蛋奶', '零食', '饮料', '调味品', '其他'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $food->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Calories with unit toggle -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">热量 (每100g)</label>
                    <div class="flex gap-1 text-xs">
                        <button type="button" @click="unit = 'kcal'" class="px-2 py-1 rounded"
                                :class="unit === 'kcal' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300'">kcal</button>
                        <button type="button" @click="unit = 'kj'" class="px-2 py-1 rounded"
                                :class="unit === 'kj' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300'">kJ</button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="calories_per_100g" step="0.1" min="0" max="10000"
                           x-model="calories" value="{{ old('calories_per_100g', $food->calories_per_100g) }}" required
                           class="flex-1 px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="unit === 'kcal' ? 'kcal' : 'kJ'"></span>
                </div>
                <input type="hidden" name="calorie_unit" :value="unit">
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    <span x-show="unit === 'kcal'">1 kcal ≈ 4.184 kJ</span>
                    <span x-show="unit === 'kj'">1 kJ ≈ 0.239 kcal</span>
                </p>
            </div>

            <!-- Optional nutrition -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">营养素 (每100g，可选)</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">蛋白质 (g)</label>
                        <input type="number" name="protein_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('protein_per_100g', $food->protein_per_100g) }}"
                               class="w-full px-2 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">碳水 (g)</label>
                        <input type="number" name="carbs_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('carbs_per_100g', $food->carbs_per_100g) }}"
                               class="w-full px-2 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">脂肪 (g)</label>
                        <input type="number" name="fat_per_100g" step="0.1" min="0" max="100"
                               value="{{ old('fat_per_100g', $food->fat_per_100g) }}"
                               class="w-full px-2 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                保存修改
            </button>
        </form>
    </div>
</body>
</html>
