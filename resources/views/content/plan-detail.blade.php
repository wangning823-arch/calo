<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>{{ $plan['title'] }} - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('content.trainingPlans') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">{{ $plan['title'] }}</h1>
                <div class="w-6"></div>
            </div>
        </div>

        <div class="px-4 mt-4 space-y-4">
            <!-- Plan Info -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex gap-2 mb-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['goal'] === 'lose' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $plan['goal'] === 'lose' ? '减脂' : '塑形' }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $plan['difficulty'] === 'beginner' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $plan['difficulty'] === 'beginner' ? '新手' : '进阶' }}
                    </span>
                </div>
                <div class="text-sm text-gray-600">
                    持续 {{ $plan['duration_weeks'] }} 周
                </div>
            </div>

            <!-- Exercises -->
            @if($plan['exercises'])
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-medium mb-3">训练动作</h3>
                <div class="space-y-2">
                    @foreach(json_decode($plan['exercises'], true) ?? [] as $exercise)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="font-medium text-sm">{{ $exercise['name'] ?? '未知动作' }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $exercise['sets'] ?? '' }}组 × {{ $exercise['reps'] ?? '' }}次
                            @if(isset($exercise['rest']))
                                · 休息{{ $exercise['rest'] }}
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Adopt Button -->
            <form method="POST" action="{{ route('content.adoptPlan', $plan['id']) }}">
                @csrf
                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    采用此计划
                </button>
            </form>

            <!-- Disclaimer -->
            <div class="bg-gray-100 rounded-xl p-4 text-xs text-gray-500">
                <p>本训练计划为一般健康信息，非医疗建议。如有特殊健康状况，请咨询医生后执行。</p>
            </div>
        </div>
    </div>
</body>
</html>
