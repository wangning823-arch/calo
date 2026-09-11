<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>记录运动 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 rounded-lg p-1.5 -ml-1.5 text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
                    <span class="text-sm">返回</span>
                </a>
                <h1 class="text-[15px] font-semibold">记录运动</h1>
                <div class="w-14"></div>
            </div>
        </div>

        @include('partials.flash')

        <form action="{{ route('exercises.store') }}" method="POST" class="px-4 mt-4 space-y-4" x-data="exerciseForm()">
            @csrf

            <!-- Date & Time -->
            <div class="card p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">运动日期</label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}"
                               min="{{ now()->subDays(30)->toDateString() }}"
                               class="input-field">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">时间</label>
                        <input type="time" name="recorded_time" value="{{ old('recorded_time', $now) }}"
                               class="input-field">
                    </div>
                </div>
            </div>

            <!-- Category filter + Exercise type -->
            @php
                $frequentIdSet = collect($frequentIds)->map(fn ($id) => (int) $id)->all();
                $hasFrequent = !empty($frequentIdSet);
            @endphp
            <div class="card p-4" x-data="{ search: '', category: '{{ $hasFrequent ? '常用' : '' }}' }">
                <label class="mb-2.5 block text-sm font-semibold">运动项目</label>

                <!-- Category tabs -->
                <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                    <button type="button" @click="category = ''"
                            class="px-3 py-1 text-xs rounded-full whitespace-nowrap transition-colors"
                            :class="category === '' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">
                        全部
                    </button>
                    @if($hasFrequent)
                        <button type="button" @click="category = '常用'"
                                class="px-3 py-1 text-xs rounded-full whitespace-nowrap transition-colors"
                                :class="category === '常用' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">
                            常用
                        </button>
                    @endif
                    @foreach($categories as $cat)
                        <button type="button" @click="category = '{{ $cat }}'"
                                class="px-3 py-1 text-xs rounded-full whitespace-nowrap transition-colors"
                                :class="category === '{{ $cat }}' ? 'bg-brand-600 text-white shadow-soft' : 'bg-black/[0.04] dark:bg-white/[0.06] text-[var(--calo-muted)]'">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>

                <!-- Search -->
                <input type="text" x-model="search" placeholder="搜索运动..." class="input-field mb-3 text-sm">

                <!-- Exercise list -->
                <div class="max-h-60 overflow-y-auto space-y-2">
                    @foreach($exerciseTypes as $type)
                        @php
                            $isFrequent = in_array((int) $type->id, $frequentIdSet, true);
                        @endphp
                        <label class="flex items-center p-2.5 border rounded-lg cursor-pointer transition-all text-sm"
                               x-show="((category === '' || category === '{{ $type->category }}' @if($isFrequent) || category === '常用' @endif)) && ('{{ $type->name }}'.includes(search) || search === '')"
                               :class="selectedId == '{{ $type->id }}' ? 'border-brand-500 bg-brand-50 dark:border-brand-400 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-brand-300 dark:hover:border-brand-700'">
                            <input type="radio" name="exercise_type_id" value="{{ $type->id }}"
                                   x-model="selectedId" class="sr-only">
                            <div class="flex-1">
                                <div class="font-medium flex items-center gap-1.5">
                                    {{ $type->name }}
                                    @if($isFrequent)
                                        <span class="rounded bg-brand-50 dark:bg-brand-900/30 px-1.5 py-0.5 text-[10px] font-normal text-brand-700 dark:text-brand-300">常用</span>
                                    @endif
                                </div>
                                <div class="text-xs text-[var(--calo-muted)]">{{ $type->category }} · MET {{ $type->met_value }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Duration -->
            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">运动时长（分钟）</label>
                <input type="number" name="duration_minutes" min="1" max="600"
                       value="{{ old('duration_minutes', 30) }}"
                       class="input-field">
            </div>

            <!-- Intensity -->
            <div class="card p-4">
                <label class="mb-2.5 block text-sm font-semibold">运动强度</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'light' ? 'border-brand-500 bg-brand-50 dark:border-brand-400 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-brand-300 dark:hover:border-brand-700'">
                        <input type="radio" name="intensity" value="light" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">🚶</div>
                        <div class="text-xs font-medium">轻度</div>
                        <div class="text-xs text-[var(--calo-muted)]">微微出汗</div>
                    </label>
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'moderate' ? 'border-brand-500 bg-brand-50 dark:border-brand-400 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-brand-300 dark:hover:border-brand-700'">
                        <input type="radio" name="intensity" value="moderate" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">🏃</div>
                        <div class="text-xs font-medium">中度</div>
                        <div class="text-xs text-[var(--calo-muted)]">明显出汗</div>
                    </label>
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'heavy' ? 'border-brand-500 bg-brand-50 dark:border-brand-400 dark:bg-brand-900/25' : 'border-[var(--calo-line)] hover:border-brand-300 dark:hover:border-brand-700'">
                        <input type="radio" name="intensity" value="heavy" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">💪</div>
                        <div class="text-xs font-medium">高强度</div>
                        <div class="text-xs text-[var(--calo-muted)]">大汗淋漓</div>
                    </label>
                </div>
            </div>

            <!-- Distance (optional) -->
            <div class="card p-4">
                <label class="mb-1.5 block text-sm font-medium">距离（km，可选）</label>
                <input type="number" name="distance_km" step="0.1" min="0" max="500"
                       value="{{ old('distance_km') }}"
                       placeholder="如跑步、骑行可填写"
                       class="input-field">
            </div>

            <!-- Estimated calories preview -->
            <div class="card p-4 text-sm" x-show="selectedId && duration > 0">
                <div class="font-medium mb-1">预计消耗</div>
                <div class="font-number text-2xl font-bold text-flame-600 dark:text-orange-300">
                    <span x-text="estimatedCalories"></span> <span class="text-sm font-normal">kcal</span>
                </div>
                <div class="text-xs text-[var(--calo-muted)] mt-1">估算值，实际消耗因人而异</div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary w-full py-3.5 text-[15px]">
                保存记录
            </button>
        </form>
    </div>

    <script>
        function exerciseForm() {
            return {
                selectedId: @json(old('exercise_type_id')),
                duration: {{ old('duration_minutes', 30) }},
                intensity: @json(old('intensity', 'moderate')),
                exerciseTypes: @json($exerciseTypes->keyBy('id')->map(fn($t) => ['met_value' => $t->met_value, 'name' => $t->name])),

                get estimatedCalories() {
                    if (!this.selectedId || !this.duration) return 0;
                    const type = this.exerciseTypes[this.selectedId];
                    if (!type) return 0;
                    const weightKg = 70;
                    const hours = this.duration / 60;
                    return Math.round((type.met_value - 1) * weightKg * hours);
                }
            }
        }
    </script>
</body>
</html>
