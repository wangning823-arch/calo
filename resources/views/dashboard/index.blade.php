<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>首页 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include('partials.sidebar')

    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell" x-data="dashboard()">
        <!-- Greeting -->
        <div class="px-4 pt-6 md:px-8 md:pt-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-[var(--calo-muted)]">{{ now()->isoFormat('YYYY年MM月DD日 dddd') }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight">
                        你好，{{ $user->name }}
                        @if($streak > 0)
                            <span class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-flame-50 dark:bg-orange-900/25 text-flame-600 dark:text-orange-300 align-middle">
                                连续 {{ $streak }} 天
                            </span>
                        @endif
                    </h1>
                </div>
                <a href="{{ route('reports.history') }}" class="shrink-0 mt-1 text-xs font-medium text-brand-700 dark:text-brand-400 hover:underline">查看报表</a>
            </div>
        </div>

        @include('partials.flash')

        <!-- Calorie budget hero -->
        <div class="px-4 mt-5 md:px-8">
            <div class="card p-6 md:p-7">
                <div class="flex flex-col items-center">
                    <div class="relative w-full max-w-md">
                        <svg class="progress-track w-full h-[104px]" viewBox="0 0 300 104" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                            <defs>
                                <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#34d399"/>
                                    <stop offset="100%" stop-color="#059669"/>
                                </linearGradient>
                            </defs>
                            <!-- Racetrack track background -->
                            <path d="M40 16 H260 A36 36 0 0 1 260 88 H40 A36 36 0 0 1 40 16 Z"
                                  fill="none" stroke="rgba(16,185,129,0.12)" stroke-width="10"/>
                            <!-- Racetrack progress -->
                            <path d="M40 16 H260 A36 36 0 0 1 260 88 H40 A36 36 0 0 1 40 16 Z"
                                  fill="none"
                                  :stroke="progressColor"
                                  stroke-width="10"
                                  stroke-linecap="round"
                                  class="progress-ring-circle"
                                  pathLength="100"
                                  stroke-dasharray="100"
                                  :stroke-dashoffset="progressOffset"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <div class="text-[2rem] leading-none font-number font-bold tracking-tight" :class="statusTextClass">
                                <span x-text="remaining != null ? remaining : '—'"></span>
                            </div>
                            <div class="mt-1.5 text-xs text-[var(--calo-muted)]">剩余 kcal</div>
                        </div>
                    </div>

                    <div class="mt-5 w-full grid grid-cols-3 gap-2">
                        <div class="rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] px-2 py-3 text-center">
                            <div class="font-number text-lg font-bold" x-text="maintenance != null ? maintenance : '—'"></div>
                            <div class="mt-0.5 text-[11px] text-[var(--calo-muted)]">平衡热量</div>
                        </div>
                        <div class="rounded-2xl bg-orange-50 dark:bg-orange-900/20 px-2 py-3 text-center">
                            <div class="font-number text-lg font-bold text-orange-600 dark:text-orange-300"
                                 x-text="targetDeficit != null ? '−' + targetDeficit : '—'"></div>
                            <div class="mt-0.5 text-[11px] text-orange-600/70 dark:text-orange-300/70">预期缺口</div>
                        </div>
                        <div class="rounded-2xl px-2 py-3 text-center"
                             :class="actualDeficitClass">
                            <div class="font-number text-lg font-bold"
                                 x-text="actualDeficitText"></div>
                            <div class="mt-0.5 text-[11px] opacity-70">实际缺口</div>
                        </div>
                    </div>

                    <div class="mt-2 w-full grid grid-cols-3 gap-2">
                        <div class="rounded-2xl bg-flame-50 dark:bg-orange-900/20 px-2 py-3 text-center">
                            <div class="font-number text-lg font-bold text-flame-600 dark:text-orange-300" x-text="intake"></div>
                            <div class="mt-0.5 text-[11px] text-flame-600/70 dark:text-orange-300/70">已摄入</div>
                        </div>
                        <div class="rounded-2xl bg-sky-50 dark:bg-sky-900/20 px-2 py-3 text-center">
                            <div class="font-number text-lg font-bold text-sky-600 dark:text-sky-300" x-text="burned"></div>
                            <div class="mt-0.5 text-[11px] text-sky-600/70 dark:text-sky-300/70">运动消耗</div>
                        </div>
                        <div class="rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] px-2 py-3 text-center">
                            <div class="font-number text-lg font-bold" x-text="baseBudget != null ? baseBudget : '—'"></div>
                            <div class="mt-0.5 text-[11px] text-[var(--calo-muted)]">目标摄入</div>
                        </div>
                    </div>

                    <p x-show="maintenance != null && actualDeficit != null" class="mt-3 text-xs text-[var(--calo-muted)] text-center">
                        平衡 <span x-text="maintenance" class="font-medium text-[var(--calo-ink)]"></span>
                        · 今日实际
                        <span class="font-medium" :class="(actualDeficit || 0) >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-red-500'"
                              x-text="actualDeficitText"></span>
                        <template x-if="targetDeficit != null && deficitGap != null">
                            <span>
                                ，相对目标
                                <span class="font-medium" :class="deficitGap >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-amber-600'"
                                      x-text="deficitGap >= 0 ? '超额 ' + deficitGap : '还差 ' + Math.abs(deficitGap)"></span>
                                kcal
                            </span>
                        </template>
                    </p>

                    <p x-show="baseBudget != null && burned > 0" class="mt-1 text-xs text-[var(--calo-muted)] text-center">
                        目标摄入 <span x-text="baseBudget" class="font-medium text-[var(--calo-ink)]"></span>
                        + 运动 <span x-text="burned" class="font-medium text-sky-600 dark:text-sky-300"></span>
                        = 今日上限 <span x-text="dynamicBudget" class="font-medium text-brand-700 dark:text-brand-400"></span> kcal
                    </p>

                    @if(!$today['budget'])
                        <div class="mt-5 w-full text-center">
                            <a href="{{ route('goals.create') }}" class="btn-primary inline-flex w-full max-w-xs items-center justify-center px-5 py-3 text-sm">设定减重目标</a>
                            <p class="mt-2 text-xs text-[var(--calo-muted)]">设定目标后可查看预期缺口与目标摄入</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="px-4 mt-4 md:px-8">
            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold">快捷操作</h3>
                    <span class="text-[11px] text-[var(--calo-muted)]">一键开始记录</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <a href="{{ route('meals.create') }}" class="group rounded-2xl border border-[var(--calo-line)] bg-white dark:bg-white/[0.03] p-3 text-center hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-soft transition">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-2xl bg-flame-50 dark:bg-orange-900/25 text-flame-600 dark:text-orange-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m4-10v12M8 8v8m8-6v10M4 10v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
                        </div>
                        <div class="text-sm font-medium">记录饮食</div>
                    </a>
                    <a href="{{ route('exercises.create') }}" class="group rounded-2xl border border-[var(--calo-line)] bg-white dark:bg-white/[0.03] p-3 text-center hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-soft transition">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 dark:bg-sky-900/25 text-sky-600 dark:text-sky-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="text-sm font-medium">记录运动</div>
                    </a>
                    <a href="{{ route('weights.create') }}" class="group rounded-2xl border border-[var(--calo-line)] bg-white dark:bg-white/[0.03] p-3 text-center hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-soft transition">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 dark:bg-violet-900/25 text-violet-600 dark:text-violet-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                        <div class="text-sm font-medium">记录体重</div>
                    </a>
                </div>

                <div class="mt-4 pt-4 border-t border-[var(--calo-line)]">
                    <h4 class="text-xs font-medium text-[var(--calo-muted)] mb-3">快速记录运动</h4>
                    <div class="flex justify-center gap-6">
                        <button type="button" @click="logQuickExercise('run5')" class="flex flex-col items-center group">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white font-bold text-lg shadow-soft group-hover:scale-105 transition-transform">5</span>
                            <span class="mt-1.5 text-xs text-[var(--calo-muted)]">5km 慢跑</span>
                        </button>
                        <button type="button" @click="logQuickExercise('run10')" class="flex flex-col items-center group">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-red-500 text-white font-bold text-lg shadow-soft group-hover:scale-105 transition-transform">10</span>
                            <span class="mt-1.5 text-xs text-[var(--calo-muted)]">10km 慢跑</span>
                        </button>
                        <button type="button" @click="logQuickExercise('strength')" class="flex flex-col items-center group">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-slate-600 to-slate-800 text-white text-xl shadow-soft group-hover:scale-105 transition-transform">💪</span>
                            <span class="mt-1.5 text-xs text-[var(--calo-muted)]">力量 1h</span>
                        </button>
                    </div>
                    <div x-show="quickExerciseMsg" x-transition x-cloak
                         class="mt-3 text-center text-sm" :class="quickExerciseOk ? 'text-brand-600' : 'text-red-500'"
                         x-text="quickExerciseMsg"></div>
                </div>
            </div>
        </div>

        <!-- Nutrition + meals -->
        <div class="px-4 mt-4 md:px-8 grid gap-4 md:grid-cols-2">
            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold">营养素摄入</h3>
                    <span class="text-[11px] text-[var(--calo-muted)]">相对目标摄入</span>
                </div>
                <div class="space-y-3.5">
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-medium">蛋白质</span>
                            <span class="font-number text-[var(--calo-muted)]" x-text="protein + 'g'"></span>
                        </div>
                        <div class="h-2 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden">
                            <div class="h-2 rounded-full bg-gradient-to-r from-rose-400 to-rose-500 transition-all duration-500" :style="'width:' + Math.min(100, proteinPercent) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-medium">碳水</span>
                            <span class="font-number text-[var(--calo-muted)]" x-text="carbs + 'g'"></span>
                        </div>
                        <div class="h-2 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden">
                            <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-orange-400 transition-all duration-500" :style="'width:' + Math.min(100, carbsPercent) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-medium">脂肪</span>
                            <span class="font-number text-[var(--calo-muted)]" x-text="fat + 'g'"></span>
                        </div>
                        <div class="h-2 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden">
                            <div class="h-2 rounded-full bg-gradient-to-r from-violet-400 to-violet-500 transition-all duration-500" :style="'width:' + Math.min(100, fatPercent) + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold">今日饮食</h3>
                    <a href="{{ route('meals.create') }}" class="text-xs font-medium text-brand-700 dark:text-brand-400">+ 添加</a>
                </div>
                @if(isset($today['by_meal']) && count($today['by_meal']) > 0)
                    <div class="space-y-1">
                        @foreach(['breakfast' => '早餐', 'lunch' => '午餐', 'dinner' => '晚餐', 'snack' => '加餐'] as $type => $label)
                            @if(isset($today['by_meal'][$type]))
                            <div class="flex items-center justify-between py-2.5 border-b border-[var(--calo-line)] last:border-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-flame-50 dark:bg-orange-900/25 text-xs text-flame-600 dark:text-orange-300">{{ mb_substr($label, 0, 1) }}</span>
                                    <span class="text-sm">{{ $label }}</span>
                                </div>
                                <span class="font-number text-sm font-semibold">{{ $today['by_meal'][$type]['calories'] }} <span class="text-[11px] font-normal text-[var(--calo-muted)]">kcal</span></span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-[var(--calo-line)] py-8 text-center">
                        <p class="text-sm text-[var(--calo-muted)]">今天还没有饮食记录</p>
                        <a href="{{ route('meals.create') }}" class="mt-2 inline-block text-sm font-medium text-brand-700 dark:text-brand-400">去记一餐 →</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent weight -->
        @if($recentWeight)
        <div class="px-4 mt-4 md:px-8">
            <a href="{{ route('weights.trend') }}" class="card flex items-center justify-between p-4 hover:shadow-lift transition-shadow">
                <div>
                    <div class="text-sm font-semibold">最近体重</div>
                    <div class="mt-0.5 text-xs text-[var(--calo-muted)]">{{ \Carbon\Carbon::parse($recentWeight['date'])->isoFormat('YYYY年MM月DD日') }}</div>
                </div>
                <div class="text-right">
                    <div class="font-number text-2xl font-bold text-violet-600 dark:text-violet-300">
                        {{ number_format($recentWeight['weight_kg'], 1) . ' kg' }}
                    </div>
                    <div class="text-[11px] text-[var(--calo-muted)]">查看趋势 →</div>
                </div>
            </a>
        </div>
        @endif

        <script>
            function dashboard() {
                return {
                    baseBudget: @json($today['budget']),
                    budget: @json($today['dynamic_budget']),
                    dynamicBudget: @json($today['dynamic_budget']),
                    maintenance: @json($today['maintenance'] ?? null),
                    targetDeficit: @json($today['target_deficit'] ?? null),
                    actualDeficit: @json($today['actual_deficit'] ?? null),
                    deficitGap: @json($today['deficit_gap'] ?? null),
                    intake: @json($today['intake_calories']),
                    burned: @json($today['burned_calories']),
                    remaining: @json($today['remaining']),
                    protein: @json($today['intake_protein']),
                    carbs: @json($today['intake_carbs']),
                    fat: @json($today['intake_fat']),
                    status: @json($today['status']),
                    showOnboarding: @json($showOnboarding ?? false),
                    quickExerciseMsg: '',
                    quickExerciseOk: false,
                    onboardingStep: 0,
                    onboardingSteps: [
                        { title: '首页看板', desc: '查看平衡热量、缺口与摄入进度', target: 'progress-ring' },
                        { title: '饮食记录', desc: '点击"记录饮食"添加每餐饮食', target: 'btn-meal' },
                        { title: '运动记录', desc: '点击"记录运动"追踪运动消耗', target: 'btn-exercise' },
                        { title: '体重记录', desc: '点击"记录体重"追踪体重变化', target: 'btn-weight' },
                    ],

                    nextOnboardingStep() {
                        if (this.onboardingStep < this.onboardingSteps.length - 1) {
                            this.onboardingStep++;
                        } else {
                            this.completeOnboarding();
                        }
                    },

                    async completeOnboarding() {
                        this.showOnboarding = false;
                        await fetch('{{ route("onboarding.complete") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                    },

                    async skipOnboarding() {
                        this.showOnboarding = false;
                        await fetch('{{ route("onboarding.skip") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                    },

                    async logQuickExercise(type) {
                        this.quickExerciseMsg = '';
                        try {
                            const res = await fetch('{{ route("exercises.quick") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ type }),
                            });
                            const data = await res.json();
                            this.quickExerciseOk = res.ok;
                            this.quickExerciseMsg = data.message || '记录失败';
                            if (res.ok) {
                                setTimeout(() => window.location.reload(), 1200);
                            }
                        } catch (e) {
                            this.quickExerciseOk = false;
                            this.quickExerciseMsg = '网络错误，请重试';
                        }
                    },

                    get proteinPercent() { return this.budget ? (this.protein / (this.budget * 0.3 / 4) * 100) : 0; },
                    get carbsPercent() { return this.budget ? (this.carbs / (this.budget * 0.5 / 4) * 100) : 0; },
                    get fatPercent() { return this.budget ? (this.fat / (this.budget * 0.2 / 9) * 100) : 0; },

                    get actualDeficitText() {
                        if (this.actualDeficit == null) return '—';
                        const v = this.actualDeficit;
                        return v >= 0 ? String(v) : '盈余 ' + Math.abs(v);
                    },

                    get actualDeficitClass() {
                        if (this.actualDeficit == null) return 'bg-black/[0.03] dark:bg-white/[0.04]';
                        if (this.actualDeficit < 0) return 'bg-red-50 dark:bg-red-900/20';
                        if (this.targetDeficit != null && this.actualDeficit >= this.targetDeficit) {
                            return 'bg-brand-50 dark:bg-brand-900/25';
                        }
                        return 'bg-amber-50 dark:bg-amber-900/20';
                    },

                    get progressOffset() {
                        if (!this.budget || this.budget <= 0) return 100;
                        const pct = Math.min(1, Math.max(0, this.intake / this.budget));
                        return 100 * (1 - pct);
                    },

                    get progressColor() {
                        if (this.status === 'over') return '#ef4444';
                        if (this.status === 'warning') return '#f59e0b';
                        return 'url(#ringGradient)';
                    },

                    get statusTextClass() {
                        if (this.status === 'over') return 'text-red-500';
                        if (this.status === 'warning') return 'text-amber-500';
                        return 'text-brand-600 dark:text-brand-400';
                    }
                }
            }
        </script>

        <!-- Onboarding -->
        <div x-show="showOnboarding" x-cloak class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/55 backdrop-blur-sm"></div>
            <div class="relative h-full flex flex-col items-center justify-center px-6">
                <div class="card w-full max-w-sm p-6 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 dark:bg-brand-900/30 text-3xl"
                         x-text="['📊','🍽️','💪','⚖️'][onboardingStep]"></div>
                    <h3 class="text-lg font-bold" x-text="onboardingSteps[onboardingStep].title"></h3>
                    <p class="mt-2 text-sm text-[var(--calo-muted)]" x-text="onboardingSteps[onboardingStep].desc"></p>

                    <div class="my-6 flex justify-center gap-2">
                        <template x-for="(step, i) in onboardingSteps" :key="i">
                            <div class="h-2 rounded-full transition-all" :class="i === onboardingStep ? 'w-6 bg-brand-500' : (i < onboardingStep ? 'w-2 bg-brand-300' : 'w-2 bg-black/10 dark:bg-white/15')"></div>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <button type="button" @click="nextOnboardingStep()" class="btn-primary w-full py-3 text-sm" x-text="onboardingStep < onboardingSteps.length - 1 ? '下一步' : '开始使用'"></button>
                        <button type="button" @click="skipOnboarding()" class="w-full py-2 text-sm text-[var(--calo-muted)] hover:text-[var(--calo-ink)]">跳过引导</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
