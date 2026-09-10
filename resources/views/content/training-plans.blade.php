<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>训练计划 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-[var(--calo-muted)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">训练计划</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4">
            @if(empty($plans))
            <div class="card p-8 text-center">
                <div class="text-[var(--calo-muted)] mb-2">暂无训练计划</div>
                <div class="text-sm text-[var(--calo-muted)]">管理员正在添加更多计划</div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($plans as $plan)
                <a href="{{ route('content.planDetail', $plan['id']) }}" class="block card p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ $plan['title'] }}</h3>
                            <div class="flex gap-2 mt-1">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['goal'] === 'lose' ? 'bg-red-50 dark:bg-red-900/25 text-red-700 dark:text-red-300' : 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-300' }}">
                                    {{ $plan['goal'] === 'lose' ? '减脂' : '塑形' }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['difficulty'] === 'beginner' ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-300' : 'bg-amber-50 dark:bg-amber-900/25 text-amber-700 dark:text-amber-300' }}">
                                    {{ $plan['difficulty'] === 'beginner' ? '新手' : '进阶' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm text-[var(--calo-muted)]">
                            {{ $plan['duration_weeks'] }}周
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</body>
</html>
