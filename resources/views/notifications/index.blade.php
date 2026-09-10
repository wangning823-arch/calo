<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>通知中心 - Calo</title>
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
                <h1 class="text-lg font-semibold">通知中心
                    @if($unreadCount > 0)
                        <span class="text-xs bg-red-500 text-white rounded-full px-2 py-0.5 ml-1">{{ $unreadCount }}</span>
                    @endif
                </h1>
                <a href="{{ route('notifications.preferences') }}" class="text-[var(--calo-muted)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-brand-800 dark:text-brand-200 text-sm shadow-soft">
                {{ session('success') }}
            </div>
        @endif

        <div class="px-4 mt-4">
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}" class="mb-4">
                @csrf
                <button type="submit" class="text-sm text-brand-700 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">
                    全部标记为已读 ({{ $unreadCount }})
                </button>
            </form>
            @endif

            @if(empty($notifications))
            <div class="card p-8 text-center">
                <div class="text-[var(--calo-muted)] mb-2">暂无通知</div>
                <div class="text-sm text-[var(--calo-muted)]">新的提醒和成就将在这里显示</div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                <div class="card p-4 {{ $notification['read_at'] ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                @if(!$notification['read_at'])
                                    <div class="w-2 h-2 bg-brand-500 rounded-full"></div>
                                @endif
                                <h3 class="text-sm font-medium">{{ $notification['title'] }}</h3>
                                @php
                                    $typeClass = match($notification['type']) {
                                        'reminder' => 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-300',
                                        'alert' => 'bg-red-50 dark:bg-red-900/25 text-red-700 dark:text-red-300',
                                        'achievement' => 'bg-amber-50 dark:bg-amber-900/25 text-amber-700 dark:text-amber-300',
                                        'system' => 'bg-black/[0.06] dark:bg-white/[0.08] text-[var(--calo-muted)]',
                                        default => 'bg-black/[0.06] dark:bg-white/[0.08] text-[var(--calo-muted)]',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $typeClass }}">{{ $notification['type'] }}</span>
                            </div>
                            <p class="text-sm text-[var(--calo-muted)] mt-1">{{ $notification['content'] }}</p>
                            <div class="text-xs text-[var(--calo-muted)] mt-2">{{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}</div>
                        </div>
                        @if(!$notification['read_at'])
                        <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                            @csrf
                            <button type="submit" class="text-xs text-brand-700 dark:text-brand-400 hover:underline ml-2">已读</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</body>
</html>
