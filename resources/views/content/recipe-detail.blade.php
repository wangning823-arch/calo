<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $recipe['title'] }} - Calo</title>
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
            .desktop-sidebar { display: flex !important; flex-direction: column; }
        }
        @media (max-width: 767px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: none !important; }
        }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen pb-20">
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('content.recipes') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">{{ $recipe['title'] }}</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4 space-y-4" x-data="{ showImport: false, mealType: '{{ $mealType }}' }">
            <!-- Nutrition Info -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div>
                        <div class="text-xl font-bold text-orange-600">{{ $recipe['total_calories'] }}</div>
                        <div class="text-xs text-gray-500">热量 kcal</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-blue-600">{{ $recipe['protein'] }}g</div>
                        <div class="text-xs text-gray-500">蛋白质</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-yellow-600">{{ $recipe['carbs'] }}g</div>
                        <div class="text-xs text-gray-500">碳水</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-red-600">{{ $recipe['fat'] }}g</div>
                        <div class="text-xs text-gray-500">脂肪</div>
                    </div>
                </div>
            </div>

            <!-- Ingredients -->
            @if($recipe['ingredients'])
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-medium mb-3">食材清单</h3>
                <div class="space-y-2">
                    @foreach(json_decode($recipe['ingredients'], true) ?? [] as $ingredient)
                    <div class="flex justify-between text-sm">
                        <span>{{ $ingredient['name'] ?? '未知' }}</span>
                        <span class="text-gray-500">{{ $ingredient['amount'] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Steps -->
            @if($recipe['steps'])
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-medium mb-3">制作步骤</h3>
                <div class="space-y-3">
                    @foreach(json_decode($recipe['steps'], true) ?? [] as $i => $step)
                    <div class="flex gap-3">
                        <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">{{ $i + 1 }}</div>
                        <div class="text-sm text-gray-700">{{ $step['step'] ?? $step }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Import Button -->
            <button @click="showImport = true" class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                一键录入到饮食记录
            </button>

            <!-- Import Modal -->
            <div x-show="showImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="max-width: 430px; margin: 0 auto;">
                <div class="bg-white rounded-xl p-6 mx-4 w-full max-w-sm">
                    <h3 class="font-medium mb-3">选择餐次</h3>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="mealType === 'breakfast' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" value="breakfast" x-model="mealType" class="mr-2"> 早餐
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="mealType === 'lunch' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" value="lunch" x-model="mealType" class="mr-2"> 午餐
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="mealType === 'dinner' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" value="dinner" x-model="mealType" class="mr-2"> 晚餐
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="mealType === 'snack' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" value="snack" x-model="mealType" class="mr-2"> 加餐
                        </label>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button @click="showImport = false" class="flex-1 py-2 border border-gray-300 rounded-lg text-sm">取消</button>
                        <form method="POST" action="{{ route('content.importRecipe', $recipe['id']) }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="meal_type" :value="mealType">
                            <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg text-sm">确认导入</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="bg-gray-100 rounded-xl p-4 text-xs text-gray-500">
                <p>本食谱为一般健康信息，非医疗建议。如有特殊健康状况，请咨询医生。</p>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
