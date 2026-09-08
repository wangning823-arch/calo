<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>成就墙 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
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
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen pb-20">
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold dark:text-white">成就墙</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            @php
                $earnedCount = collect($badges)->filter(fn($b) => $b['earned'])->count();
                $totalCount = count($badges);
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $earnedCount }}/{{ $totalCount }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">已获得成就</div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $totalCount > 0 ? ($earnedCount / $totalCount * 100) : 0 }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                @foreach($badges as $key => $badge)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 text-center {{ $badge['earned'] ? '' : 'opacity-50' }}">
                    <div class="text-4xl mb-2">{{ $badge['icon'] }}</div>
                    <div class="text-sm font-medium {{ $badge['earned'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ $badge['name'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge['description'] }}</div>
                    @if($badge['earned'] && $badge['earned_at'])
                        <div class="text-xs text-green-600 mt-2">
                            {{ \Carbon\Carbon::parse($badge['earned_at'])->format('m/d获得') }}
                        </div>
                    @elseif(!$badge['earned'])
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">未获得</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    </div>
</body>
</html>
