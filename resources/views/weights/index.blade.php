<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>体重记录 - Calo</title>
    @include('partials.app-scripts')
</head>
<body >
    @include('partials.sidebar')
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
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

        <!-- Date filter -->
        <div class="px-4 mt-4">
            <form method="GET" action="{{ route('weights.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3">
                <div class="flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[160px] max-w-xs">
                        <label for="date" class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">日期</label>
                        <input type="date" id="date" name="date" value="{{ $date }}"
                               class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-2.5 py-2 text-sm text-gray-900 dark:text-white">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">查询</button>
                        <a href="{{ route('weights.index', ['date' => now()->toDateString()]) }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400">今天</a>
                    </div>
                </div>
            </form>
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
                                            {{ number_format((float)$record->weight_kg, 1) . ' kg' }}
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
                    <div class="text-gray-400 mb-2">该日暂无体重记录</div>
                    <a href="{{ route('weights.create') }}" class="text-blue-500 text-sm">去记录体重</a>
                </div>
            @endif
        </div>

        </div>
</body>
</html>
