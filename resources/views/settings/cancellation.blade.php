<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>账号注销 - Calo</title>
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
                <h1 class="text-lg font-semibold">账号注销</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="px-4 mt-4">
            @if($isPending)
                <!-- Pending Cancellation -->
                <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold">注销冷静期</h2>
                            <p class="text-xs text-gray-500">您的账号将在冷静期结束后被注销</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 rounded-lg p-3 text-sm text-yellow-700 mb-4">
                        <p>冷静期截止：<strong>{{ $user->cancellation_deadline->format('Y年m月d日 H:i') }}</strong></p>
                        <p class="mt-1">冷静期内可撤销注销，逾期将永久删除所有数据。</p>
                    </div>

                    <!-- Data deletion notice -->
                    <div class="bg-red-50 rounded-lg p-3 text-sm text-red-700 mb-4">
                        <p class="font-medium mb-1">以下数据将被永久删除：</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>饮食记录</li>
                            <li>运动记录</li>
                            <li>体重记录</li>
                            <li>减重目标</li>
                            <li>收藏食物</li>
                            <li>成就记录</li>
                            <li>通知记录</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('settings.cancellation.cancel') }}">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                            撤销注销
                        </button>
                    </form>
                </div>
            @else
                <!-- Request Cancellation -->
                <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold">申请注销账号</h2>
                            <p class="text-xs text-gray-500">注销后将无法恢复</p>
                        </div>
                    </div>

                    <div class="bg-red-50 rounded-lg p-3 text-sm text-red-700 mb-4">
                        <p class="font-medium mb-1">注销将导致以下后果：</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>所有个人数据将被永久删除</li>
                            <li>7天冷静期内可撤销注销</li>
                            <li>冷静期过后数据无法恢复</li>
                            <li>已获得的成就记录将丢失</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('settings.cancellation.request') }}">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors" onclick="return confirm('确定要申请注销账号吗？')">
                            申请注销
                        </button>
                    </form>
                </div>
            @endif

            <!-- Execute Cancellation (for testing, after deadline) -->
            @if($isPending && $user->cancellation_deadline && $user->cancellation_deadline->isPast())
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h3 class="font-semibold mb-2">执行注销</h3>
                <p class="text-sm text-gray-600 mb-3">冷静期已过，请输入密码确认执行注销。</p>
                <form method="POST" action="{{ route('settings.cancellation.execute') }}">
                    @csrf
                    <input type="password" name="password" placeholder="输入密码" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm mb-3" required>
                    <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors" onclick="return confirm('此操作不可逆！确定要注销吗？')">
                        确认注销
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    </div>
</body>
</html>
