<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>通知中心 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <h1 class="text-lg font-semibold">通知中心
                    @if($unreadCount > 0)
                        <span class="text-xs bg-red-500 text-white rounded-full px-2 py-0.5 ml-1">{{ $unreadCount }}</span>
                    @endif
                </h1>
                <a href="{{ route('notifications.preferences') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="px-4 mt-4">
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}" class="mb-4">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                    全部标记为已读 ({{ $unreadCount }})
                </button>
            </form>
            @endif

            @if(empty($notifications))
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="text-gray-400 mb-2">暂无通知</div>
                <div class="text-sm text-gray-500">新的提醒和成就将在这里显示</div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                <div class="bg-white rounded-xl shadow-sm p-4 {{ $notification['read_at'] ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                @if(!$notification['read_at'])
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                @endif
                                <h3 class="text-sm font-medium">{{ $notification['title'] }}</h3>
                                @php
                                    $typeClass = match($notification['type']) {
                                        'reminder' => 'bg-blue-100 text-blue-700',
                                        'alert' => 'bg-red-100 text-red-700',
                                        'achievement' => 'bg-yellow-100 text-yellow-700',
                                        'system' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $typeClass }}">{{ $notification['type'] }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification['content'] }}</p>
                            <div class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}</div>
                        </div>
                        @if(!$notification['read_at'])
                        <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 ml-2">已读</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    </div>
</body>
</html>
