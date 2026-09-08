<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>通知偏好 - Calo</title>
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
                <a href="{{ route('notifications.index') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">通知偏好</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="px-4 mt-4">
            <form method="POST" action="{{ route('notifications.updatePreferences') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Meal Reminders -->
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h3 class="font-medium mb-3">饮食提醒</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium">早餐提醒</div>
                                <div class="text-xs text-gray-500">提醒记录早餐</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="time" name="breakfast_time" value="{{ $preferences['breakfast_time'] }}"
                                       class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_breakfast" value="1"
                                           {{ $preferences['notification_breakfast'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium">午餐提醒</div>
                                <div class="text-xs text-gray-500">提醒记录午餐</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="time" name="lunch_time" value="{{ $preferences['lunch_time'] }}"
                                       class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_lunch" value="1"
                                           {{ $preferences['notification_lunch'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium">晚餐提醒</div>
                                <div class="text-xs text-gray-500">提醒记录晚餐</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="time" name="dinner_time" value="{{ $preferences['dinner_time'] }}"
                                       class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_dinner" value="1"
                                           {{ $preferences['notification_dinner'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Reminders -->
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h3 class="font-medium mb-3">其他提醒</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium">称重提醒</div>
                                <div class="text-xs text-gray-500">提醒记录体重</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="time" name="weigh_in_time" value="{{ $preferences['weigh_in_time'] }}"
                                       class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_weigh_in" value="1"
                                           {{ $preferences['notification_weigh_in'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cannot disable alerts -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">健康预警通知</p>
                            <p class="text-xs mt-0.5">健康预警通知不可关闭，以确保您的安全。</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    保存设置
                </button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
