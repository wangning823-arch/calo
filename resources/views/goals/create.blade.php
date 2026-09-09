<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>设定目标 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">设定减重目标</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mx-4 mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700 text-sm">
                <p class="font-medium mb-1">⚠️ 温馨提示</p>
                <p>{{ session('warning') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Current Weight -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-sm font-medium text-gray-500 mb-2">当前体重</h2>
                @if($currentWeight)
                    <div class="text-3xl font-bold text-blue-600">
                        {{ $user->unit_preference === 'jin' ? number_format($currentWeight * 2, 1) . ' 斤' : number_format($currentWeight, 1) . ' kg' }}
                    </div>
                @else
                    <div class="text-gray-400">
                        暂无记录
                        <a href="{{ route('weights.create') }}" class="text-blue-500 ml-2">去记录</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Goal Form -->
        <div class="px-4 mt-4">
            <form action="{{ route('goals.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-4 space-y-4" x-data="goalForm()">
                @csrf

                <!-- Target Weight -->
                <div>
                    <label for="target_weight" class="block text-sm font-medium text-gray-700 mb-1">目标体重 ({{ $user->unit_preference === 'jin' ? '斤' : 'kg' }})</label>
                    <input type="number" id="target_weight" name="target_weight" step="0.1" min="30" max="300"
                           value="{{ old('target_weight', $defaultTargetWeight) }}"
                           x-model="targetWeight"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="请输入目标体重">
                    @if($defaultTargetWeight)
                        <p class="mt-1 text-xs text-gray-400">推荐值：基于BMI 22计算的理想体重 {{ $defaultTargetWeight }} kg</p>
                    @endif
                </div>

                <!-- Target Date -->
                <div>
                    <label for="target_date" class="block text-sm font-medium text-gray-700 mb-1">目标日期</label>
                    <input type="date" id="target_date" name="target_date"
                           value="{{ old('target_date', $defaultTargetDate) }}"
                           x-model="targetDate"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    @if($defaultTargetDate)
                        <p class="mt-1 text-xs text-gray-400">推荐值：按每周减1kg计算，预计 {{ \Carbon\Carbon::parse($defaultTargetDate)->format('Y年m月d日') }} 达成</p>
                    @endif
                </div>

                <!-- Calculated Results -->
                <div class="bg-gray-50 rounded-lg p-4 text-sm" x-show="targetWeight && targetDate && isValidGoal">
                    <div class="font-medium text-gray-700 mb-2">系统计算结果</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">需减重</span>
                            <span class="font-medium text-red-500" x-text="weightToLose + ' kg'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">剩余天数</span>
                            <span class="font-medium" x-text="daysRemaining + ' 天'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">每周减重</span>
                            <span class="font-medium" :class="weeklyRate > 1.0 ? 'text-red-500' : 'text-green-600'" x-text="weeklyRate + ' kg/周'"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex justify-between">
                            <span class="text-gray-700 font-medium">每日热量缺口</span>
                            <span class="font-bold text-orange-500" x-text="'-' + dailyDeficit + ' kcal'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700 font-medium">每日热量预算</span>
                            <span class="font-bold text-blue-600" x-text="dailyBudget + ' kcal'"></span>
                        </div>
                        <div x-show="rawBudget < minCalories" class="text-xs text-gray-400">
                            理论预算 <span x-text="rawBudget"></span> kcal 低于安全线 <span x-text="minCalories"></span> kcal，已按最低值计算
                        </div>
                    </div>
                    <!-- Warning for budget floor -->
                    <div x-show="isBudgetFloored" class="mt-3 p-2 rounded text-xs bg-red-50 text-red-600">
                        ⚠️ 按您的TDEE（<span x-text="tdee"></span>kcal）和最低安全摄入（<span x-text="minCalories"></span>kcal），最大安全缺口仅 <span x-text="maxSafeDeficit"></span>kcal/天。当前目标需要 <span x-text="dailyDeficit"></span>kcal/天，建议延长目标日期至 <span x-text="realisticDate"></span> 左右。
                    </div>
                    <!-- Warning for extreme plans -->
                    <div x-show="!isBudgetFloored && weeklyRate > 1.0" class="mt-3 p-2 rounded text-xs"
                         :class="weeklyRate > 1.5 ? 'bg-red-50 text-red-600' : 'bg-yellow-50 text-yellow-700'">
                        <span x-show="weeklyRate > 1.5">⚠️ 减重速率较快，可能影响健康。建议将目标日期延后。</span>
                        <span x-show="weeklyRate > 1.0 && weeklyRate <= 1.5">⚠️ 减重速率略高于推荐值（1kg/周），请量力而行。</span>
                    </div>
                </div>

                <!-- No goal hint -->
                <div x-show="!targetWeight || !targetDate || !isValidGoal" class="bg-gray-50 rounded-lg p-3 text-sm text-gray-400 text-center">
                    请输入目标体重和日期，系统将自动计算热量缺口
                </div>

                <!-- Confirm Warning -->
                @if(session('warning'))
                    <label class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg cursor-pointer">
                        <input type="checkbox" name="confirm_warning" value="1" class="w-4 h-4 text-yellow-600 rounded">
                        <span class="ml-2 text-sm text-yellow-700">我已了解风险，确认继续</span>
                    </label>
                @endif

                <!-- Submit -->
                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    设定目标
                </button>
            </form>
        </div>

        <!-- Disclaimer -->
        <div class="px-4 mt-4">
            <div class="bg-gray-100 rounded-xl p-4 text-xs text-gray-500">
                <p class="font-medium text-gray-600 mb-1">⚠️ 免责声明</p>
                <p>本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。如有特殊健康状况，请咨询医生后制定计划。</p>
            </div>
        </div>

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('meals.create') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-xs mt-0.5">记录</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-0.5">我的</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function goalForm() {
            const currentWeight = {{ $currentWeight ?: 70 }};
            const tdee = {{ $tdee }};
            const minCalories = {{ $minCalories }};
            const KCAL_PER_KG = 7700;

            return {
                targetWeight: '{{ old("target_weight", $defaultTargetWeight) }}',
                targetDate: '{{ old("target_date", $defaultTargetDate) }}',
                get isValidGoal() {
                    if (!this.targetWeight || !this.targetDate) return false;
                    const tw = parseFloat(this.targetWeight);
                    return tw > 0 && tw < currentWeight;
                },
                get weightToLose() {
                    if (!this.isValidGoal) return 0;
                    return (currentWeight - parseFloat(this.targetWeight)).toFixed(1);
                },
                get daysRemaining() {
                    if (!this.targetDate) return 0;
                    return Math.max(1, Math.ceil((new Date(this.targetDate) - new Date()) / (24 * 60 * 60 * 1000)));
                },
                get weeklyRate() {
                    if (!this.isValidGoal) return 0;
                    const weeks = Math.max(1, this.daysRemaining / 7);
                    return ((currentWeight - parseFloat(this.targetWeight)) / weeks).toFixed(1);
                },
                get dailyDeficit() {
                    if (!this.isValidGoal) return 0;
                    const deficit = Math.round((parseFloat(this.weightToLose) * KCAL_PER_KG) / this.daysRemaining);
                    return Math.max(300, Math.min(2000, deficit));
                },
                get rawBudget() {
                    return this.isValidGoal ? Math.round(tdee - this.dailyDeficit) : 0;
                },
                get dailyBudget() {
                    return Math.max(minCalories, this.rawBudget);
                },
                get isBudgetFloored() {
                    return this.isValidGoal && this.rawBudget < minCalories;
                },
                get maxSafeDeficit() {
                    return Math.max(0, Math.round(tdee - minCalories));
                },
                get realisticDate() {
                    if (!this.isValidGoal || this.maxSafeDeficit <= 0) return '';
                    const days = Math.ceil((parseFloat(this.weightToLose) * KCAL_PER_KG) / this.maxSafeDeficit);
                    const d = new Date();
                    d.setDate(d.getDate() + days);
                    return `${d.getFullYear()}年${d.getMonth()+1}月${d.getDate()}日`;
                }
            }
        }
    </script>
    </div>
</body>
</html>
