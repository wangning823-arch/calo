<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>设定目标 - Calo</title>
    @include('partials.app-scripts')
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
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-[var(--calo-muted)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">{{ $currentGoal ? '修改减重目标' : '设定减重目标' }}</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-brand-800 dark:text-brand-200 text-sm shadow-soft">
                {{ session('success') }}
            </div>
        @endif

        @if($currentGoal)
            <div class="mx-4 mt-4 p-3 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-brand-800 dark:text-brand-200 text-sm">
                已有进行中的目标，下方已填入当前设定；修改后保存将覆盖原目标。
            </div>
        @endif

        @if(session('warning'))
            <div class="mx-4 mt-4 p-3 rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 text-sm">
                <p class="font-medium mb-1">⚠️ 温馨提示</p>
                <p>{{ session('warning') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 rounded-xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm shadow-soft">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Current Weight -->
        <div class="px-4 mt-4">
            <div class="card p-4">
                <h2 class="text-sm font-medium text-[var(--calo-muted)] mb-2">当前体重</h2>
                @if($currentWeight)
                    <div class="text-3xl font-bold text-brand-600 dark:text-brand-400">
                        {{ number_format($currentWeight, 1) . ' kg' }}
                    </div>
                @else
                    <div class="text-[var(--calo-muted)]">
                        暂无记录
                        <a href="{{ route('weights.create') }}" class="text-brand-700 dark:text-brand-400 ml-2">去记录</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Goal Form -->
        <div class="px-4 mt-4">
            <form action="{{ route('goals.store') }}" method="POST" class="card p-4 space-y-4" x-data="goalForm()">
                @csrf

                <!-- Target Weight -->
                <div>
                    <label for="target_weight" class="block text-sm font-medium mb-1">目标体重 (kg)</label>
                    <input type="number" id="target_weight" name="target_weight" step="0.1" min="30" max="150"
                           value="{{ old('target_weight', $defaultTargetWeight) }}"
                           x-model="targetWeight"
                           class="input-field text-sm"
                           placeholder="请输入目标体重">
                    @if($usingRecommendation && $defaultTargetWeight)
                        <p class="mt-1 text-xs text-[var(--calo-muted)]">推荐值：基于BMI 22计算的理想体重 {{ $defaultTargetWeight }} kg</p>
                    @elseif($currentGoal)
                        <p class="mt-1 text-xs text-[var(--calo-muted)]">当前设定：{{ number_format((float) $currentGoal->target_weight, 1) }} kg</p>
                    @endif
                </div>

                <!-- Target Date -->
                <div>
                    <label for="target_date" class="block text-sm font-medium mb-1">目标日期</label>
                    <input type="date" id="target_date" name="target_date"
                           value="{{ old('target_date', $defaultTargetDate) }}"
                           x-model="targetDate"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="input-field text-sm">
                    @if($usingRecommendation && $defaultTargetDate)
                        <p class="mt-1 text-xs text-[var(--calo-muted)]">推荐值：按每周减 0.5kg 计算（约需每日缺口 550 kcal），预计 {{ \Carbon\Carbon::parse($defaultTargetDate)->format('Y年m月d日') }} 达成</p>
                    @elseif($currentGoal)
                        <p class="mt-1 text-xs text-[var(--calo-muted)]">当前设定：{{ $currentGoal->target_date->format('Y年m月d日') }}</p>
                    @endif
                </div>

                <!-- Calculated Results -->
                <div class="bg-black/[0.03] dark:bg-white/[0.04] rounded-lg p-4 text-sm" x-show="targetWeight && targetDate && isValidGoal">
                    <div class="font-medium mb-2">系统计算结果</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-[var(--calo-muted)]">平衡热量（体重不变）</span>
                            <span class="font-medium text-brand-600 dark:text-brand-400" x-text="tdee + ' kcal'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--calo-muted)]">需减重</span>
                            <span class="font-medium text-red-500 dark:text-red-400" x-text="weightToLose + ' kg'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--calo-muted)]">剩余天数</span>
                            <span class="font-medium" x-text="daysRemaining + ' 天'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--calo-muted)]">每周减重</span>
                            <span class="font-medium" :class="weeklyRate > 1.0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-400'" x-text="weeklyRate + ' kg/周'"></span>
                        </div>
                        <div class="border-t border-[var(--calo-line)] pt-2 flex justify-between">
                            <span class="font-medium">每日预期缺口</span>
                            <span class="font-bold text-orange-500 dark:text-orange-300" x-text="plannedDeficit + ' kcal/天'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">每日目标摄入</span>
                            <span class="font-bold text-brand-600 dark:text-brand-400" x-text="dailyBudget + ' kcal'"></span>
                        </div>
                        <div class="text-xs text-[var(--calo-muted)]">
                            目标摄入 = 平衡热量 − 预期缺口 = <span x-text="tdee"></span> − <span x-text="plannedDeficit"></span> = <span x-text="dailyBudget"></span>
                        </div>
                    </div>
                    <!-- Warning when deficit exceeds max safe -->
                    <div x-show="isOverSafe" class="mt-3 p-2 rounded text-xs bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                        ⚠️ 按平衡热量（<span x-text="tdee"></span> kcal）与最低安全摄入（<span x-text="minCalories"></span> kcal），
                        最大安全缺口约 <span x-text="maxSafeDeficit"></span> kcal/天。
                        当前目标需 <span x-text="plannedDeficit"></span> kcal/天，可能偏激进。
                        提交时需确认；确认后仍按您设定的数值生效。建议延长目标日期至 <span x-text="realisticDate"></span> 附近。
                    </div>
                    <!-- Warning for extreme plans -->
                    <div x-show="!isOverSafe && weeklyRate > 1.0" class="mt-3 p-2 rounded text-xs"
                         :class="weeklyRate > 1.5 ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200'">
                        <span x-show="weeklyRate > 1.5">⚠️ 减重速率较快，可能影响健康。建议将目标日期延后。</span>
                        <span x-show="weeklyRate > 1.0 && weeklyRate <= 1.5">⚠️ 减重速率略高于推荐值（1kg/周），请量力而行。</span>
                    </div>
                </div>

                <!-- No goal hint -->
                <div x-show="!targetWeight || !targetDate || !isValidGoal" class="bg-black/[0.03] dark:bg-white/[0.04] rounded-lg p-3 text-sm text-[var(--calo-muted)] text-center">
                    请输入目标体重和日期，系统将自动计算预期缺口与目标摄入
                </div>

                <!-- Confirm Warning -->
                @if(session('warning'))
                    <label class="flex items-center p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="confirm_warning" value="1" class="w-4 h-4 text-yellow-600 dark:text-yellow-400 rounded">
                        <span class="ml-2 text-sm text-yellow-700 dark:text-yellow-300">我已了解风险，确认继续</span>
                    </label>
                @endif

                <!-- Submit -->
                <button type="submit" class="btn-primary w-full py-3 text-sm">
                    {{ $currentGoal ? '保存修改' : '设定目标' }}
                </button>
            </form>
        </div>

        <!-- Disclaimer -->
        <div class="px-4 mt-4">
            <div class="bg-black/[0.03] dark:bg-white/[0.04] rounded-xl p-4 text-xs text-[var(--calo-muted)]">
                <p class="font-medium text-[var(--calo-muted)] mb-1">⚠️ 免责声明</p>
                <p>本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。如有特殊健康状况，请咨询医生后制定计划。</p>
            </div>
        </div>
</div>

    <script>
        function goalForm() {
            const currentWeight = {{ $currentWeight ?: 70 }};
            const tdee = Math.round({{ $tdee }});
            const minCalories = {{ $minCalories }};
            const maxSafeDeficit = Number('{{ (int) ($maxSafeDeficit ?? 0) }}');
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
                // Schedule-driven deficit: how much/day is needed to hit target by the date
                get requiredDeficit() {
                    if (!this.isValidGoal) return 0;
                    return Math.round((parseFloat(this.weightToLose) * KCAL_PER_KG) / this.daysRemaining);
                },
                // What the user chose — always respected, never rewritten
                get plannedDeficit() {
                    return this.requiredDeficit;
                },
                get isOverSafe() {
                    return this.isValidGoal && this.requiredDeficit > maxSafeDeficit;
                },
                // Keep maxSafeDeficit on the component for templates
                get maxSafeDeficit() {
                    return maxSafeDeficit;
                },
                get rawBudget() {
                    return this.isValidGoal ? Math.round(tdee - this.requiredDeficit) : 0;
                },
                get dailyBudget() {
                    return this.rawBudget;
                },
                get realisticDate() {
                    if (!this.isValidGoal || maxSafeDeficit <= 0) return '';
                    const days = Math.ceil((parseFloat(this.weightToLose) * KCAL_PER_KG) / maxSafeDeficit);
                    const d = new Date();
                    d.setDate(d.getDate() + days);
                    return `${d.getFullYear()}年${d.getMonth()+1}月${d.getDate()}日`;
                }
            }
        }
    </script>
</body>
</html>
