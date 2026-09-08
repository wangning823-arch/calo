<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>体重记录 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
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
                <h1 class="text-lg font-semibold dark:text-white">体重记录</h1>
                <a href="{{ route('weights.create') }}" class="text-blue-500 text-sm font-medium">+ 新增</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="px-4 mt-4 flex gap-2">
            <a href="{{ route('weights.trend') }}" class="flex-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 text-center text-sm text-blue-600 dark:text-blue-400 font-medium">
                📈 查看趋势
            </a>
        </div>

        <!-- Records -->
        <div class="px-4 mt-4">
            @if($records->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($records as $record)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium dark:text-white">
                                            {{ $user->unit_preference === 'jin' ? number_format((float)$record->weight_kg * 2, 1) . ' 斤' : number_format((float)$record->weight_kg, 1) . ' kg' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $record->date->format('Y年m月d日') }}
                                        @if($record->body_fat_percentage)
                                            · 体脂 {{ $record->body_fat_percentage }}%
                                        @endif
                                        @if($record->waist_cm)
                                            · 腰围 {{ $record->waist_cm }}cm
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex gap-2">
                                        <a href="{{ route('weights.edit', $record) }}" class="text-xs text-blue-500">编辑</a>
                                        <form method="POST" action="{{ route('weights.destroy', $record) }}" onsubmit="return confirm('确定删除这条体重记录？')">
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
                    <div class="text-gray-400 mb-2">暂无体重记录</div>
                    <a href="{{ route('weights.create') }}" class="text-blue-500 text-sm">去记录体重</a>
                </div>
            @endif
        </div>

        <!-- Mobile Bottom Nav -->
        <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-20 md:hidden">
            <div class="flex justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs mt-0.5">首页</span>
                </a>
                <a href="{{ route('weights.create') }}" class="flex flex-col items-center px-3 py-1 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-xs mt-0.5">记录</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center px-3 py-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-xs mt-0.5">我的</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
