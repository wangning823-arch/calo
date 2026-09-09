<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>收藏夹 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <!-- Header -->
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="w-6"></div>
                <h1 class="text-lg font-semibold">收藏夹</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Favorites List -->
        <div class="px-4 mt-4">
            @if($favorites->isEmpty())
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-2">暂无收藏</div>
                    <a href="{{ route('foods.search') }}" class="text-blue-500 text-sm">去搜索食物</a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($favorites as $food)
                        <div class="bg-white rounded-lg p-3 shadow-sm flex items-center justify-between">
                            <a href="{{ route('foods.show', $food) }}" class="flex-1">
                                <div class="text-sm font-medium text-gray-800">{{ $food->name }}</div>
                                <div class="text-xs text-gray-500">{{ $food->category }} · {{ $food->calories_per_100g }} kcal/100g</div>
                            </a>
                            <form action="{{ route('foods.favorite', $food) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-red-500 p-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
</div>
</body>
</html>
