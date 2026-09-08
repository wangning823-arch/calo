<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>记录运动 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
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
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">记录运动</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('exercises.store') }}" method="POST" class="px-4 mt-4 space-y-4" x-data="exerciseForm()">
            @csrf

            <!-- Date -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">运动日期</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                       max="{{ now()->toDateString() }}"
                       min="{{ now()->subDays(30)->toDateString() }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Category filter + Exercise type -->
            <div class="bg-white rounded-xl shadow-sm p-4" x-data="{ search: '', category: '' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">运动项目</label>

                <!-- Category tabs -->
                <div class="flex gap-2 mb-3 overflow-x-auto pb-2">
                    <button type="button" @click="category = ''"
                            class="px-3 py-1 text-xs rounded-full whitespace-nowrap transition-colors"
                            :class="category === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">
                        全部
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" @click="category = '{{ $cat }}'"
                                class="px-3 py-1 text-xs rounded-full whitespace-nowrap transition-colors"
                                :class="category === '{{ $cat }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>

                <!-- Search -->
                <input type="text" x-model="search" placeholder="搜索运动..."
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-3 focus:ring-2 focus:ring-blue-500">

                <!-- Exercise list -->
                <div class="max-h-60 overflow-y-auto space-y-2">
                    @foreach($exerciseTypes as $type)
                        <label class="flex items-center p-2.5 border rounded-lg cursor-pointer transition-all text-sm"
                               x-show="(category === '' || category === '{{ $type->category }}') && ('{{ $type->name }}'.includes(search) || search === '')"
                               :class="selectedId == '{{ $type->id }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="exercise_type_id" value="{{ $type->id }}"
                                   x-model="selectedId" class="sr-only">
                            <div class="flex-1">
                                <div class="font-medium">{{ $type->name }}</div>
                                <div class="text-xs text-gray-500">{{ $type->category }} · MET {{ $type->met_value }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Duration -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">运动时长（分钟）</label>
                <input type="number" name="duration_minutes" min="1" max="600"
                       value="{{ old('duration_minutes', 30) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Intensity -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">运动强度</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'light' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="intensity" value="light" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">🚶</div>
                        <div class="text-xs font-medium">轻度</div>
                        <div class="text-xs text-gray-400">微微出汗</div>
                    </label>
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'moderate' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="intensity" value="moderate" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">🏃</div>
                        <div class="text-xs font-medium">中度</div>
                        <div class="text-xs text-gray-400">明显出汗</div>
                    </label>
                    <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                           :class="intensity === 'heavy' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="intensity" value="heavy" x-model="intensity" class="sr-only">
                        <div class="text-lg mb-1">💪</div>
                        <div class="text-xs font-medium">高强度</div>
                        <div class="text-xs text-gray-400">大汗淋漓</div>
                    </label>
                </div>
            </div>

            <!-- Distance (optional) -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">距离（km，可选）</label>
                <input type="number" name="distance_km" step="0.1" min="0" max="500"
                       value="{{ old('distance_km') }}"
                       placeholder="如跑步、骑行可填写"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Estimated calories preview -->
            <div class="bg-gray-50 rounded-xl p-4 text-sm" x-show="selectedId && duration > 0">
                <div class="font-medium text-gray-700 mb-1">预计消耗</div>
                <div class="text-2xl font-bold text-green-600">
                    <span x-text="estimatedCalories"></span> kcal
                </div>
                <div class="text-xs text-gray-400 mt-1">估算值，实际消耗因人而异</div>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                保存记录
            </button>
        </form>

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('exercises.create') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
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
    </div>
</body>
</html>
