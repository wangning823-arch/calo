<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>历史统计 - Calo</title>
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
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0"
         x-data="calendarPage()" x-init="loadCalendar()">

        <!-- Header -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">历史统计</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <!-- Calendar Navigation -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-4">
                    <button @click="prevMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="text-center">
                        <h2 class="text-lg font-semibold" x-text="monthLabel"></h2>
                        <div class="flex gap-4 text-xs text-gray-500 mt-0.5" x-show="summary">
                            <span>记录 <span class="font-medium" x-text="summary?.record_days"></span>/<span x-text="summary?.total_days"></span>天</span>
                            <span>运动 <span class="font-medium text-green-600" x-text="summary?.exercise_days"></span>天</span>
                        </div>
                    </div>
                    <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Weekday Headers -->
                <div class="grid grid-cols-7 gap-1 mb-1">
                    <template x-for="d in ['一','二','三','四','五','六','日']">
                        <div class="text-center text-xs text-gray-400 py-1" x-text="d"></div>
                    </template>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <!-- Empty cells before first day -->
                    <template x-for="n in leadingEmptyDays" :key="'empty-'+n">
                        <div></div>
                    </template>
                    <!-- Day cells -->
                    <template x-for="day in days" :key="day.date">
                        <div class="min-h-[52px] sm:min-h-[72px] rounded-lg p-1 text-center relative border"
                             :class="day.has_records ? (day.deficit > 0 ? 'bg-green-50 border-green-200' : (day.deficit !== null && day.deficit < -200 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200')) : 'bg-transparent border-transparent'">
                            <!-- Day number -->
                            <div class="text-xs font-medium"
                                 :class="isToday(day.date) ? 'text-blue-600 font-bold' : (day.has_records ? 'text-gray-800' : 'text-gray-300')"
                                 x-text="day.day"></div>
                            <!-- Deficit -->
                            <template x-if="day.deficit !== null">
                                <div class="text-[10px] leading-tight mt-0.5 font-medium"
                                     :class="day.deficit > 0 ? 'text-green-600' : 'text-red-500'">
                                    <span x-text="day.deficit > 0 ? '+' + day.deficit : day.deficit"></span>
                                </div>
                            </template>
                            <!-- Exercise icons -->
                            <template x-if="day.exercise_icons.length > 0">
                                <div class="flex justify-center gap-0.5 mt-0.5 flex-wrap">
                                    <template x-for="icon in day.exercise_icons" :key="icon">
                                        <span class="text-[10px] leading-none"
                                              x-text="icon === '💪' ? '💪' : icon + 'km'"
                                              :class="icon === '💪' ? '' : 'text-green-600 font-medium'"></span>
                                    </template>
                                </div>
                            </template>
                            <!-- Today indicator -->
                            <div x-show="isToday(day.date)" class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-blue-500"></div>
                        </div>
                    </template>
                </div>

                <!-- Legend -->
                <div class="flex justify-center gap-4 mt-3 text-[10px] text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-blue-500"></span>今天</span>
                    <span class="flex items-center gap-1"><span class="text-green-600 font-medium">5km</span>5公里</span>
                    <span class="flex items-center gap-1"><span class="text-green-600 font-medium">10km</span>10公里</span>
                    <span class="flex items-center gap-1">💪力量</span>
                </div>
            </div>
        </div>

        <!-- Monthly Summary -->
        <div x-show="summary" class="px-4 mt-3">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">月度汇总</h3>
                <div class="grid grid-cols-2 gap-3 text-center text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs">日均摄入</div>
                        <div class="font-bold text-orange-500" x-text="summary?.avg_intake || '-'"></div>
                        <div class="text-[10px] text-gray-400">kcal</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs">日均缺口</div>
                        <div class="font-bold" :class="(summary?.avg_deficit || 0) > 0 ? 'text-green-600' : 'text-gray-600'" x-text="summary?.avg_deficit != null ? (summary.avg_deficit > 0 ? '+' : '') + summary.avg_deficit : '-'"></div>
                        <div class="text-[10px] text-gray-400">kcal</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs">总运动消耗</div>
                        <div class="font-bold text-green-600" x-text="summary?.total_burned || '-'"></div>
                        <div class="text-[10px] text-gray-400">kcal</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs">记录完成率</div>
                        <div class="font-bold text-blue-600" x-text="(summary?.completion_rate || 0) + '%'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prediction Section -->
        @if($prediction || $progress)
        <div class="px-4 mt-3 mb-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">目标预测</h3>

                @if($progress)
                <div class="mb-3">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $progress['progress'] }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-blue-600">{{ $progress['progress'] }}%</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div>
                            <div class="font-bold text-green-600">{{ $progress['lost_jin'] }}斤</div>
                            <div class="text-gray-400">已减</div>
                        </div>
                        <div>
                            <div class="font-bold">{{ $user->unit_preference === 'jin' ? round($progress['current_weight'] * 2, 1) . '斤' : round($progress['current_weight'], 1) . 'kg' }}</div>
                            <div class="text-gray-400">当前</div>
                        </div>
                        <div>
                            <div class="font-bold text-blue-600">{{ $user->unit_preference === 'jin' ? round($progress['target_weight'] * 2, 1) . '斤' : round($progress['target_weight'], 1) . 'kg' }}</div>
                            <div class="text-gray-400">目标</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($prediction)
                    @if(($prediction['predictable'] ?? false) && ($prediction['achieved'] ?? false))
                        <div class="text-center py-3">
                            <div class="text-2xl mb-1">🎉</div>
                            <div class="text-sm font-bold text-green-600">{{ $prediction['message'] }}</div>
                        </div>
                    @elseif($prediction['predictable'] ?? false)
                        <div class="flex items-center gap-3 p-3 rounded-lg {{ ($prediction['on_track'] ?? false) ? 'bg-green-50' : 'bg-yellow-50' }}">
                            <div class="text-lg">{{ ($prediction['on_track'] ?? false) ? '✅' : '⚠️' }}</div>
                            <div class="text-sm">{{ $prediction['message'] }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                            <div class="bg-gray-50 rounded-lg p-2 text-center">
                                <div class="text-gray-400">还需减</div>
                                <div class="font-bold text-orange-600">{{ $prediction['weight_remaining'] ?? '-' }}斤</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2 text-center">
                                <div class="text-gray-400">预计还需</div>
                                <div class="font-bold">{{ $prediction['days_to_goal'] ?? '-' }}天</div>
                            </div>
                        </div>
                    @else
                        <div class="bg-blue-50 rounded-lg p-3 text-xs text-blue-700">{{ $prediction['message'] }}</div>
                    @endif
                @endif
            </div>
        </div>
        @endif

        <!-- Plateau -->
        @if($plateau)
        <div class="px-4 mt-3 mb-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <h3 class="font-medium text-yellow-800 text-sm mb-1">📊 平台期提醒</h3>
                <p class="text-xs text-yellow-700 mb-2">{{ $plateau['message'] }}</p>
                <div class="text-xs text-yellow-700 space-y-0.5">
                    @foreach($plateau['suggestions'] as $s)
                        <div>· {{ $s }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        function calendarPage() {
            const now = new Date();
            return {
                year: now.getFullYear(),
                month: now.getMonth() + 1,
                monthLabel: '',
                days: [],
                summary: null,
                leadingEmptyDays: 0,
                loading: false,

                isToday(dateStr) {
                    return dateStr === new Date().toISOString().slice(0, 10);
                },

                prevMonth() {
                    this.month--;
                    if (this.month < 1) { this.month = 12; this.year--; }
                    this.loadCalendar();
                },

                nextMonth() {
                    this.month++;
                    if (this.month > 12) { this.month = 1; this.year++; }
                    this.loadCalendar();
                },

                async loadCalendar() {
                    this.loading = true;
                    try {
                        const res = await fetch(`/api/calendar/${this.year}/${this.month}`);
                        const data = await res.json();
                        this.monthLabel = data.month_label;
                        this.days = data.days;
                        this.summary = data.summary;

                        // Calculate leading empty days (Monday-based week)
                        const firstDay = new Date(this.year, this.month - 1, 1);
                        let dow = firstDay.getDay(); // 0=Sun
                        dow = dow === 0 ? 6 : dow - 1; // Convert to Mon=0
                        this.leadingEmptyDays = dow;
                    } catch (e) {
                        console.error('Failed to load calendar', e);
                    }
                    this.loading = false;
                }
            }
        }
    </script>
</body>
</html>
