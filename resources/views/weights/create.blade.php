<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>记录体重 - Calo</title>
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
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">记录体重</h1>
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

        <!-- Latest weight display -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-sm font-medium text-gray-500 mb-2">上次记录</h2>
                @if($latestWeight)
                    <div class="text-3xl font-bold text-blue-600">
                        {{ $latestWeight['weight_display'] }}
                        <span class="text-base font-normal text-gray-500">{{ $latestWeight['unit'] === 'jin' ? '斤' : 'kg' }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ $latestWeight['date'] }}</div>
                @else
                    <div class="text-gray-400">暂无记录</div>
                @endif
            </div>
        </div>

        <!-- Weight form -->
        <form action="{{ route('weights.store') }}" method="POST" class="px-4 mt-4 space-y-4"
              x-data="{ unit: '{{ $user->unit_preference ?? 'jin' }}', weight: '{{ old('weight', $latestWeight['weight_display'] ?? '') }}' }">
            @csrf
            <input type="hidden" name="unit" :value="unit">

            <!-- Date -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">日期</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                       max="{{ now()->toDateString() }}"
                       min="{{ now()->subDays(30)->toDateString() }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>

            <!-- Weight -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">体重</label>
                    <div class="flex gap-1 text-xs">
                        <button type="button" @click="unit = 'jin'" class="px-2 py-1 rounded"
                                :class="unit === 'jin' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">斤</button>
                        <button type="button" @click="unit = 'kg'" class="px-2 py-1 rounded"
                                :class="unit === 'kg' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">kg</button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="weight" step="0.1"
                           x-model="weight"
                           value="{{ old('weight', $latestWeight['weight_display'] ?? '') }}"
                           placeholder="{{ $user->unit_preference === 'jin' ? '例：130' : '例：65' }}"
                           class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-sm text-gray-500" x-text="unit === 'jin' ? '斤' : 'kg'"></span>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    <span x-show="unit === 'jin'">范围：20-300斤</span>
                    <span x-show="unit === 'kg'">范围：10-150kg</span>
                </p>
            </div>

            <!-- Optional measurements -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">可选测量数据</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">体脂率 (%)</label>
                        <input type="number" name="body_fat_percentage" step="0.1" min="1" max="60"
                               value="{{ old('body_fat_percentage') }}"
                               placeholder="可选"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">腰围 (cm)</label>
                            <input type="number" name="waist_cm" step="0.1" min="30" max="200"
                                   value="{{ old('waist_cm') }}"
                                   placeholder="可选"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">臀围 (cm)</label>
                            <input type="number" name="hip_cm" step="0.1" min="30" max="200"
                                   value="{{ old('hip_cm') }}"
                                   placeholder="可选"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700">
                单日波动±2斤属正常，请关注7日均线趋势。
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                保存记录
            </button>
        </form>

        <!-- Recent records -->
        @if($recentRecords->count() > 0)
        <div class="px-4 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">最近记录</h3>
                <div class="space-y-2">
                    @foreach($recentRecords->take(10) as $record)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <span class="text-sm text-gray-600">{{ $record->date->format('m/d') }}</span>
                            <span class="text-sm font-medium">
                                {{ $user->unit_preference === 'jin' ? number_format((float)$record->weight_kg * 2, 1) . ' 斤' : number_format((float)$record->weight_kg, 1) . ' kg' }}
                            </span>
                            <div class="flex gap-2">
                                <a href="{{ route('weights.edit', $record) }}" class="text-xs text-blue-500">编辑</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('weights.create') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
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
    </div>
</body>
</html>
