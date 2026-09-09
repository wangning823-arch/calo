@php
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

<!-- Mobile Sidebar System -->
<div x-data="{ sidebarOpen: false }">
    <!-- Mobile Header Bar -->
    <div class="mobile-header-bar bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-30 md:hidden">
        <div class="px-4 py-3 flex items-center justify-between">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 dark:text-gray-400 p-1 -ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-semibold dark:text-white">Calo</h1>
            <div class="w-8"></div>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-cloak
         @click.self="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-40 md:hidden">
    </div>

    <!-- Mobile Sidebar Drawer -->
    <aside x-show="sidebarOpen" x-cloak
           x-transition:enter="transition-transform ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 shadow-xl z-50 flex flex-col md:hidden overflow-y-auto">
    <div class="p-5 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-green-600">Calo</h1>
            <p class="text-xs text-gray-400 mt-1">热量管理，轻松减重</p>
        </div>
        <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <nav class="flex-1 px-3 space-y-0.5">
        <a href="{{ route('dashboard') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $currentRoute === 'dashboard' ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            首页
        </a>
        <a href="{{ route('meals.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'meals') ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            饮食记录
        </a>
        <a href="{{ route('exercises.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'exercises') ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            运动记录
        </a>
        <a href="{{ route('weights.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'weights') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            体重记录
        </a>
        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('foods.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'foods') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                食物库
            </a>
            <a href="{{ route('content.recipes') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'content.recipe') ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                食谱
            </a>
            <a href="{{ route('content.trainingPlans') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'content.training') ? 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                训练计划
            </a>
            <a href="{{ route('predictions.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'predictions') ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                历史统计
            </a>
            <a href="{{ route('reports.history') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'reports') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                报表
            </a>
            <a href="{{ route('exports.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'exports') ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                数据导出
            </a>
            <a href="{{ route('achievements.index') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'achievements') ? 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                成就
            </a>
        </div>
    </nav>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('profile.edit') }}" @click.prevent="const url = $event.target.closest('a').href; sidebarOpen = false; setTimeout(() => { window.location.href = url; }, 50)" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'profile') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            我的
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" @click="sidebarOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                退出登录
            </button>
        </form>
    </div>
</aside>
</div><!-- end mobile sidebar system -->

<!-- Desktop Sidebar (fixed, no animation) -->
<aside class="desktop-sidebar w-60 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 fixed top-0 left-0 h-full z-30 overflow-y-auto" style="display:none;">
    <div class="p-5">
        <h1 class="text-xl font-bold text-green-600">Calo</h1>
        <p class="text-xs text-gray-400 mt-1">热量管理，轻松减重</p>
    </div>
    <nav class="flex-1 px-3 space-y-0.5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $currentRoute === 'dashboard' ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            首页
        </a>
        <a href="{{ route('meals.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'meals') ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            饮食记录
        </a>
        <a href="{{ route('exercises.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'exercises') ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            运动记录
        </a>
        <a href="{{ route('weights.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'weights') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            体重记录
        </a>
        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('foods.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'foods') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                食物库
            </a>
            <a href="{{ route('content.recipes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'content.recipe') ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                食谱
            </a>
            <a href="{{ route('content.trainingPlans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'content.training') ? 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                训练计划
            </a>
            <a href="{{ route('predictions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'predictions') ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                历史统计
            </a>
            <a href="{{ route('reports.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'reports') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                报表
            </a>
            <a href="{{ route('exports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'exports') ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                数据导出
            </a>
            <a href="{{ route('achievements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'achievements') ? 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                成就
            </a>
        </div>
    </nav>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'profile') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            我的
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                退出登录
            </button>
        </form>
    </div>
</aside>
