<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>运动记录 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    @include('partials.sidebar')
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">运动记录</h1>
                <a href="{{ route('exercises.create') }}" class="text-blue-500 text-sm font-medium">+ 新增</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Records -->
        <div class="px-4 mt-4">
            @if($records->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($records as $record)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium dark:text-white">{{ $record->exerciseType->name ?? '未知运动' }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            {{ $record->intensity === 'heavy' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' :
                                               ($record->intensity === 'moderate' ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                               'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400') }}">
                                            {{ $record->intensity === 'heavy' ? '高强度' : ($record->intensity === 'moderate' ? '中强度' : '低强度') }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $record->date->format('m/d') }}
                                        @if($record->recorded_at)
                                            {{ $record->recorded_at->format('H:i') }}
                                        @endif
                                        · {{ $record->duration_minutes }}分钟
                                        @if($record->distance_km)
                                            · {{ $record->distance_km }}km
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-orange-500">{{ round($record->estimated_calories) }} kcal</div>
                                    <div class="flex gap-2 mt-1">
                                        <a href="{{ route('exercises.edit', $record) }}" class="text-xs text-blue-500">编辑</a>
                                        <form method="POST" action="{{ route('exercises.destroy', $record) }}" onsubmit="return confirm('确定删除这条运动记录？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500">删除</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $records->links() }}</div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                    <div class="text-gray-400 mb-2">暂无运动记录</div>
                    <a href="{{ route('exercises.create') }}" class="text-blue-500 text-sm">去记录运动</a>
                </div>
            @endif
        </div>

        </body>
</html>
