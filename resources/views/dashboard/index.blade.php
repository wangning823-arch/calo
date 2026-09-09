<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Calo - 热量管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .progress-ring { transform: rotate(-90deg); }
        .progress-ring-circle { transition: stroke-dashoffset 0.5s ease; }
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .mobile-header-bar { display: none !important; }
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
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0" x-data="{ sidebarOpen: false, ...dashboard() }">

        <!-- Date Display -->
        <div class="px-4 pt-4 md:px-6 md:pt-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->isoFormat('YYYY年MM月DD日 dddd') }} · {{ $user->name }}</p>
        </div>

        <!-- Streak Banner -->
        @if($streak > 0)
        <div class="mx-4 mt-4 px-4 py-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg text-center">
            <span class="text-sm text-orange-600 dark:text-orange-400">🔥 连续{{ $streak }}天记录</span>
        </div>
        @endif

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Calorie Budget Card -->
        <div class="px-4 mt-4 md:px-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-center mb-4">
                    <!-- Progress Ring -->
                    <div class="relative">
                        <svg class="progress-ring w-40 h-40">
                            <circle cx="80" cy="80" r="70" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                            <circle cx="80" cy="80" r="70"
                                    :stroke="progressColor"
                                    stroke-width="8" fill="none"
                                    stroke-linecap="round"
                                    class="progress-ring-circle"
                                    :stroke-dasharray="439.82"
                                    :stroke-dashoffset="progressOffset"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-3xl font-bold" :class="statusTextClass">
                                <span x-text="remaining != null ? remaining : '-'"></span>
                            </div>
                            <div class="text-xs text-gray-500">剩余 kcal</div>
                        </div>
                    </div>
                </div>

                <!-- Budget Info -->
                <div class="grid grid-cols-3 gap-4 text-center mt-4">
                    <div>
                        <div class="text-lg font-bold text-gray-800" x-text="budget != null ? budget : '-'"></div>
                        <div class="text-xs text-gray-500">预算</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-orange-500" x-text="intake"></div>
                        <div class="text-xs text-gray-500">已摄入</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-blue-500" x-text="burned"></div>
                        <div class="text-xs text-gray-500">已消耗</div>
                    </div>
                </div>
                @if(!$today['budget'])
                <div class="mt-4 text-center">
                    <a href="{{ route('goals.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">设定减重目标</a>
                    <p class="text-xs text-gray-400 mt-2">设定目标后即可查看每日热量预算</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Two-column layout on desktop -->
        <div class="md:grid md:grid-cols-2 md:gap-6 md:px-6 md:mt-4">
        <!-- Nutrition Progress -->
        <div class="px-4 mt-4 md:px-0 md:mt-0">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">营养素摄入</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">蛋白质</span>
                            <span class="text-gray-500" x-text="protein + 'g'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full transition-all duration-500" :style="'width:' + Math.min(100, proteinPercent) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">碳水</span>
                            <span class="text-gray-500" x-text="carbs + 'g'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full transition-all duration-500" :style="'width:' + Math.min(100, carbsPercent) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">脂肪</span>
                            <span class="text-gray-500" x-text="fat + 'g'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" :style="'width:' + Math.min(100, fatPercent) + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="px-4 mt-4 md:px-0 md:mt-0">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">快捷操作</h3>
                <div class="grid grid-cols-3 gap-3">
                <a href="{{ route('meals.create') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">记录饮食</div>
                </a>
                <a href="{{ route('exercises.create') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">记录运动</div>
                </a>
                <a href="{{ route('weights.create') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">记录体重</div>
                </a>
                </div>

                <!-- Quick Exercise Buttons -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-xs text-gray-400 mb-3">快速记录运动</h4>
                    <div class="flex justify-center gap-5">
                        <button @click="logQuickExercise('run5')"
                                class="flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-white font-bold text-lg shadow-md group-hover:scale-110 transition-transform cursor-pointer">
                                5
                            </div>
                            <span class="text-xs text-gray-400 mt-1">5km慢跑</span>
                        </button>
                        <button @click="logQuickExercise('run10')"
                                class="flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center text-white font-bold text-lg shadow-md group-hover:scale-110 transition-transform cursor-pointer">
                                10
                            </div>
                            <span class="text-xs text-gray-400 mt-1">10km慢跑</span>
                        </button>
                        <button @click="logQuickExercise('strength')"
                                class="flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-white text-xl shadow-md group-hover:scale-110 transition-transform cursor-pointer">
                                💪
                            </div>
                            <span class="text-xs text-gray-400 mt-1">力量1h</span>
                        </button>
                    </div>
                    <div x-show="quickExerciseMsg" x-transition x-cloak
                         class="mt-3 text-center text-sm" :class="quickExerciseOk ? 'text-green-600' : 'text-red-500'"
                         x-text="quickExerciseMsg"></div>
                </div>
            </div>
        </div>
        </div> <!-- close md:grid -->

        <!-- Recent Weight & Today's Meals -->
        <div class="md:grid md:grid-cols-2 md:gap-6 md:px-6 md:mt-4">
        @if($recentWeight)
        <div class="px-4 mt-4 md:px-0 md:mt-0">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium text-gray-700">最近体重</div>
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($recentWeight['date'])->format('m/d') }}</div>
                    </div>
                    <div class="text-xl font-bold text-blue-600">
                        {{ $user->unit_preference === 'jin' ? number_format($recentWeight['weight_kg'] * 2, 1) . ' 斤' : number_format($recentWeight['weight_kg'], 1) . ' kg' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Today's Meals -->
        <div class="px-4 mt-4 md:px-0 md:mt-0 mb-4 md:mb-0">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-medium text-gray-700">今日饮食</h3>
                    <a href="{{ route('meals.create') }}" class="text-xs text-blue-500">+ 添加</a>
                </div>
                @if(isset($today['by_meal']) && count($today['by_meal']) > 0)
                    <div class="space-y-2">
                        @foreach(['breakfast' => '早餐', 'lunch' => '午餐', 'dinner' => '晚餐', 'snack' => '加餐'] as $type => $label)
                            @if(isset($today['by_meal'][$type]))
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                <span class="text-sm text-gray-600">{{ $label }}</span>
                                <span class="text-sm font-medium">{{ $today['by_meal'][$type]['calories'] }} kcal</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-400 text-sm">今天还没有饮食记录</div>
                @endif
            </div>
        </div>
        </div> <!-- close md:grid -->

        <!-- close main content wrapper -->

    <script>
        function dashboard() {
            return {
                budget: @json($today['budget']),
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
                    { title: '首页看板', desc: '查看今日热量预算和摄入进度', target: 'progress-ring' },
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

                get progressOffset() {
                    if (!this.budget || this.budget <= 0) return 439.82;
                    const pct = Math.min(1, this.intake / this.budget);
                    return 439.82 * (1 - pct);
                },

                get progressColor() {
                    if (this.status === 'over') return '#ef4444';
                    if (this.status === 'warning') return '#f59e0b';
                    return '#22c55e';
                },

                get statusTextClass() {
                    if (this.status === 'over') return 'text-red-500';
                    if (this.status === 'warning') return 'text-yellow-500';
                    return 'text-green-500';
                }
            }
        }
    </script>

    <!-- Onboarding Overlay -->
    <div x-show="showOnboarding" x-cloak class="fixed inset-0 z-50" style="max-width: 430px; margin: 0 auto;">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative h-full flex flex-col items-center justify-center px-8">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl" x-text="['📊','🍽️','💪','⚖️'][onboardingStep]"></span>
                </div>
                <h3 class="text-lg font-semibold mb-2" x-text="onboardingSteps[onboardingStep].title"></h3>
                <p class="text-sm text-gray-600 mb-6" x-text="onboardingSteps[onboardingStep].desc"></p>

                <!-- Step indicators -->
                <div class="flex justify-center gap-2 mb-6">
                    <template x-for="(step, i) in onboardingSteps" :key="i">
                        <div class="w-2 h-2 rounded-full transition-colors" :class="i <= onboardingStep ? 'bg-blue-500' : 'bg-gray-300'"></div>
                    </template>
                </div>

                <div class="space-y-2">
                    <button @click="nextOnboardingStep()" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors" x-text="onboardingStep < onboardingSteps.length - 1 ? '下一步' : '开始使用'"></button>
                    <button @click="skipOnboarding()" class="w-full py-2 text-gray-500 text-sm hover:text-gray-700 transition-colors">跳过引导</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
