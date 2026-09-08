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
                           value="{{ old('target_weight') }}"
                           x-model="targetWeight"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="请输入目标体重">
                </div>

                <!-- Target Date -->
                <div>
                    <label for="target_date" class="block text-sm font-medium text-gray-700 mb-1">目标日期</label>
                    <input type="date" id="target_date" name="target_date"
                           value="{{ old('target_date') }}"
                           x-model="targetDate"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                <!-- Daily Deficit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">每日热量缺口</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="deficit == 300 ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="target_deficit" value="300" x-model="deficit" class="sr-only">
                            <div class="flex-1">
                                <div class="text-sm font-medium">温和减重</div>
                                <div class="text-xs text-gray-500">每周减约0.6斤</div>
                            </div>
                            <div class="text-xs text-gray-400">-300kcal</div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="deficit == 500 ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="target_deficit" value="500" x-model="deficit" class="sr-only">
                            <div class="flex-1">
                                <div class="text-sm font-medium">标准减重（推荐）</div>
                                <div class="text-xs text-gray-500">每周减约1斤</div>
                            </div>
                            <div class="text-xs text-gray-400">-500kcal</div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                               :class="deficit == 750 ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="target_deficit" value="750" x-model="deficit" class="sr-only">
                            <div class="flex-1">
                                <div class="text-sm font-medium">快速减重</div>
                                <div class="text-xs text-gray-500">每周减约1.5斤</div>
                            </div>
                            <div class="text-xs text-gray-400">-750kcal</div>
                        </label>
                    </div>
                </div>

                <!-- Preview -->
                <div class="bg-gray-50 rounded-lg p-3 text-sm" x-show="targetWeight && targetDate">
                    <div class="font-medium text-gray-700 mb-1">预计效果</div>
                    <div class="space-y-1 text-gray-600">
                        <div>每周减重：<span class="font-medium" x-text="weeklyRate + ' kg'"></span></div>
                        <div>剩余天数：<span class="font-medium" x-text="daysRemaining + ' 天'"></span></div>
                        <div>预计达成：<span class="font-medium" x-text="targetDate"></span></div>
                    </div>
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
            return {
                targetWeight: '{{ old("target_weight") }}',
                targetDate: '{{ old("target_date") }}',
                deficit: '{{ old("target_deficit", 500) }}',
                get weeklyRate() {
                    if (!this.targetWeight || !this.targetDate) return '-';
                    const currentWeight = {{ $currentWeight ?: 70 }};
                    const weeks = Math.max(1, Math.ceil((new Date(this.targetDate) - new Date()) / (7 * 24 * 60 * 60 * 1000)));
                    const loss = currentWeight - parseFloat(this.targetWeight);
                    return loss > 0 ? (loss / weeks).toFixed(1) : '-';
                },
                get daysRemaining() {
                    if (!this.targetDate) return '-';
                    return Math.ceil((new Date(this.targetDate) - new Date()) / (24 * 60 * 60 * 1000));
                }
            }
        }
    </script>
    </div>
</body>
</html>
