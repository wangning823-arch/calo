<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>编辑档案 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-[var(--calo-muted)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">编辑档案</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-brand-800 dark:text-brand-200 text-sm shadow-soft">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 rounded-xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm shadow-soft">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Current Stats -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h2 class="text-sm font-medium text-[var(--calo-muted)] mb-3">当前数据</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $bmr ? number_format($bmr, 0) : '-' }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">基础代谢(BMR)</div>
                        <div class="text-xs text-[var(--calo-muted)]">kcal/天</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $tdee ? number_format($tdee, 0) : '-' }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">平衡热量(TDEE)</div>
                        <div class="text-xs text-[var(--calo-muted)]">kcal/天 · 体重不变</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $bmi ?: '-' }}</div>
                        <div class="text-xs text-[var(--calo-muted)]">BMI指数</div>
                        <div class="text-xs text-[var(--calo-muted)]">{{ $bmi ? ($bmi < 18.5 ? '偏瘦' : ($bmi < 24 ? '正常' : ($bmi < 28 ? '偏胖' : '肥胖'))) : '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="px-4 mt-4">
            <form action="{{ route('profile.update') }}" method="POST" class="card p-4 space-y-4">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">昵称</label>
                    <input type="text" id="name" name="name" maxlength="50"
                           value="{{ old('name', $user->name) }}"
                           class="input-field text-sm"
                           placeholder="请输入昵称">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium mb-2">性别</label>
                    <div class="flex gap-3" x-data="{ selected: '{{ old('gender', $user->gender) }}' }">
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'male' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-[var(--calo-line)]'">
                            <input type="radio" name="gender" value="male" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium">男</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="selected === 'female' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-[var(--calo-line)]'">
                            <input type="radio" name="gender" value="female" x-model="selected" class="sr-only">
                            <span class="text-sm font-medium">女</span>
                        </label>
                    </div>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium mb-1">出生日期</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                           max="{{ now()->subYears(10)->format('Y-m-d') }}"
                           min="1920-01-01"
                           class="input-field text-sm"
                           placeholder="请选择出生日期">
                    @if($age)
                        <p class="mt-1 text-xs text-[var(--calo-muted)]">当前 {{ $age }} 岁</p>
                    @endif
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Height -->
                <div>
                    <label for="height" class="block text-sm font-medium mb-1">身高 (cm)</label>
                    <input type="number" id="height" name="height" step="0.1" min="30" max="250"
                           value="{{ old('height', $user->height) }}"
                           class="input-field text-sm"
                           placeholder="请输入身高">
                </div>

                <!-- Current Weight (read-only, from latest record) -->
                <div>
                    <label class="block text-sm font-medium mb-1">当前体重</label>
                    <div class="px-3 py-2.5 bg-black/[0.03] dark:bg-white/[0.04] rounded-lg text-[var(--calo-muted)] text-sm">
                        @php
                            $latestWeight = $user->weightRecords()->latest('date')->first();
                        @endphp
                        @if($latestWeight)
                            {{ number_format((float)$latestWeight->weight_kg, 1) . ' kg' }}
                            <span class="text-xs text-[var(--calo-muted)] ml-2">记录于 {{ $latestWeight->date->format('m/d') }}</span>
                        @else
                            暂无记录
                            <a href="{{ route('weights.create') }}" class="text-brand-700 dark:text-brand-400 ml-2">去记录</a>
                        @endif
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-primary w-full py-3 text-sm">
                    保存修改
                </button>
            </form>
        </div>

        <!-- Goal Section -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h2 class="text-sm font-medium text-[var(--calo-muted)] mb-3">减重目标</h2>
                @if($currentGoal)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm font-medium">
                                    目标体重：{{ number_format($currentGoal->target_weight, 1) . ' kg' }}
                                </div>
                                <div class="text-xs text-[var(--calo-muted)]">
                                    目标日期：{{ \Carbon\Carbon::parse($currentGoal->target_date)->format('Y年m月d日') }}
                                </div>
                                <div class="text-xs text-[var(--calo-muted)]">
                                    目标摄入：{{ $currentGoal->daily_calorie_budget }} kcal · 预期缺口：{{ $currentGoal->target_deficit }} kcal
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $currentGoal->status === 'active' ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-300' : 'bg-black/[0.06] dark:bg-white/[0.08] text-[var(--calo-muted)]' }}">
                                {{ $currentGoal->status === 'active' ? '进行中' : '已完成' }}
                            </span>
                        </div>
                        @if($currentWeight && $currentGoal->start_weight > $currentGoal->target_weight)
                            @php
                                $totalToLose = $currentGoal->start_weight - $currentGoal->target_weight;
                                $lost = $currentGoal->start_weight - $currentWeight;
                                $progress = max(0, min(100, ($lost / $totalToLose) * 100));
                            @endphp
                            <div class="w-full bg-black/[0.06] dark:bg-white/[0.08] rounded-full h-2">
                                <div class="bg-brand-600 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="text-xs text-[var(--calo-muted)] text-center">已完成 {{ number_format($progress, 1) }}%</div>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('goals.create') }}" class="btn-primary flex-1 py-2 text-sm text-center">修改目标</a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-sm text-[var(--calo-muted)] mb-3">还没有设定减重目标</p>
                        <a href="{{ route('goals.create') }}" class="btn-primary inline-flex px-4 py-2 text-sm">设定目标</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- BMR Formula Info -->
        <div class="px-4 mt-4">
            <div class="bg-black/[0.03] dark:bg-white/[0.04] rounded-xl p-4 text-xs text-[var(--calo-muted)]">
                <p class="font-medium text-[var(--calo-muted)] mb-1">计算公式 (Mifflin-St Jeor)</p>
                <p>男：BMR = 10×体重(kg) + 6.25×身高(cm) − 5×年龄 + 5</p>
                <p>女：BMR = 10×体重(kg) + 6.25×身高(cm) − 5×年龄 − 161</p>
                <p class="mt-1">基础消耗 = BMR × 1.2（久坐系数）</p>
                <p>运动消耗通过运动记录自动计算</p>
            </div>
        </div>
</div>
</body>
</html>
