<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>编辑档案 - Calo</title>
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
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">编辑档案</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Current Stats -->
        <div class="px-4 mt-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">当前数据</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $bmr ? number_format($bmr, 0) : '-' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">基础代谢(BMR)</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">kcal/天</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ $tdee ? number_format($tdee, 0) : '-' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">每日消耗(TDEE)</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">kcal/天</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-600">{{ $bmi ?: '-' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">BMI指数</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">{{ $bmi ? ($bmi < 18.5 ? '偏瘦' : ($bmi < 24 ? '正常' : ($bmi < 28 ? '偏胖' : '肥胖'))) : '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="px-4 mt-4">
            <form action="{{ route('profile.update') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 space-y-4">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">昵称</label>
                    <input type="text" id="name" name="name" maxlength="50"
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white"
                           placeholder="请输入昵称">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">性别</label>
                    <div class="flex gap-3" x-data="{ selected: '{{ old('gender', $user->gender) }}' }">
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'male' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                            <input type="radio" name="gender" value="male" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium dark:text-white">男</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'female' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                            <input type="radio" name="gender" value="female" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium dark:text-white">女</span>
                        </label>
                    </div>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">出生日期</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                           max="{{ now()->subYears(10)->format('Y-m-d') }}"
                           min="1920-01-01"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white"
                           placeholder="请选择出生日期">
                    @if($age)
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">当前 {{ $age }} 岁</p>
                    @endif
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Height -->
                <div>
                    <label for="height" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">身高 (cm)</label>
                    <input type="number" id="height" name="height" step="0.1" min="30" max="250"
                           value="{{ old('height', $user->height) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white dark:bg-gray-700 dark:text-white"
                           placeholder="请输入身高">
                </div>

                <!-- Current Weight (read-only, from latest record) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">当前体重</label>
                    <div class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-600 dark:text-gray-300 text-sm">
                        @php
                            $latestWeight = $user->weightRecords()->latest('date')->first();
                        @endphp
                        @if($latestWeight)
                            {{ $user->unit_preference === 'jin' ? number_format((float)$latestWeight->weight_kg * 2, 1) . ' 斤' : $latestWeight->weight_kg . ' kg' }}
                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">记录于 {{ $latestWeight->date->format('m/d') }}</span>
                        @else
                            暂无记录
                            <a href="{{ route('weights.create') }}" class="text-blue-500 ml-2">去记录</a>
                        @endif
                    </div>
                </div>

                <!-- Unit Preference -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">体重单位</label>
                    <div class="flex gap-3" x-data="{ selected: '{{ old('unit_preference', $user->unit_preference) }}' }">
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'jin' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                            <input type="radio" name="unit_preference" value="jin" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium dark:text-white">斤</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'kg' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                            <input type="radio" name="unit_preference" value="kg" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium dark:text-white">kg</span>
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    保存修改
                </button>
            </form>
        </div>

        <!-- Goal Section -->
        <div class="px-4 mt-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">减重目标</h2>
                @if($currentGoal)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm font-medium dark:text-white">
                                    目标体重：{{ $user->unit_preference === 'jin' ? number_format($currentGoal->target_weight * 2, 1) . ' 斤' : number_format($currentGoal->target_weight, 1) . ' kg' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    目标日期：{{ \Carbon\Carbon::parse($currentGoal->target_date)->format('Y年m月d日') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    每日预算：{{ $currentGoal->daily_calorie_budget }} kcal · 缺口：{{ $currentGoal->target_deficit }} kcal
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $currentGoal->status === 'active' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $currentGoal->status === 'active' ? '进行中' : '已完成' }}
                            </span>
                        </div>
                        @if($currentWeight && $currentGoal->start_weight > $currentGoal->target_weight)
                            @php
                                $totalToLose = $currentGoal->start_weight - $currentGoal->target_weight;
                                $lost = $currentGoal->start_weight - $currentWeight;
                                $progress = max(0, min(100, ($lost / $totalToLose) * 100));
                            @endphp
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 text-center">已完成 {{ number_format($progress, 1) }}%</div>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('goals.create') }}" class="flex-1 py-2 bg-blue-500 text-white text-center text-sm rounded-lg hover:bg-blue-600 transition">修改目标</a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">还没有设定减重目标</p>
                        <a href="{{ route('goals.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">设定目标</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- BMR Formula Info -->
        <div class="px-4 mt-4">
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400">
                <p class="font-medium text-gray-600 dark:text-gray-300 mb-1">计算公式 (Mifflin-St Jeor)</p>
                <p>男：BMR = 10×体重(kg) + 6.25×身高(cm) − 5×年龄 + 5</p>
                <p>女：BMR = 10×体重(kg) + 6.25×身高(cm) − 5×年龄 − 161</p>
                <p class="mt-1">基础消耗 = BMR × 1.2（久坐系数）</p>
                <p>运动消耗通过运动记录自动计算</p>
            </div>
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
                <a href="{{ route('meals.create') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-xs mt-0.5">记录</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-0.5">我的</span>
                </a>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
