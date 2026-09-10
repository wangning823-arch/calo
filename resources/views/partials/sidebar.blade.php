@php
    $route = request()->route()?->getName() ?? '';
    $isActive = function (string $prefix) use ($route) {
        if ($prefix === 'dashboard') {
            return $route === 'dashboard';
        }
        return str_starts_with($route, $prefix);
    };
    $navClass = function (bool $active) {
        return 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ' . ($active
            ? 'nav-item-active'
            : 'text-[var(--calo-muted)] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] hover:text-[var(--calo-ink)]');
    };
    $isDashboard = $route === 'dashboard';
@endphp

<div x-data="{ drawerOpen: false }" @calo:toggle-sidebar.window="drawerOpen = !drawerOpen" @keydown.escape.window="drawerOpen = false">

<!-- Global home shortcut (hidden on dashboard itself) -->
@if(! $isDashboard)
<a href="{{ route('dashboard') }}"
   class="fixed z-40 bottom-20 right-4 md:bottom-6 md:right-6 inline-flex items-center gap-1.5 rounded-full bg-brand-600 text-white shadow-lift px-3.5 py-2.5 text-xs font-semibold hover:bg-brand-700 active:scale-95 transition"
   aria-label="返回首页" title="返回首页">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span class="hidden sm:inline">首页</span>
</a>
@endif

<!-- Mobile top bar with hamburger -->
<div class="mobile-header-bar app-header sticky top-0 z-30 md:hidden">
    <div class="px-3 py-2.5 flex items-center justify-between">
        <button type="button" @click="drawerOpen = true" class="p-2 -ml-1 rounded-lg text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="打开菜单">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ route('dashboard') }}" class="text-center" aria-label="返回首页">
            <div class="text-[15px] font-semibold tracking-tight">Calo</div>
            <div class="text-[11px] text-[var(--calo-muted)] -mt-0.5">热量管理，轻松减重</div>
        </a>
        <button type="button" onclick="const t=localStorage.getItem('theme'); const isDark=t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches); localStorage.setItem('theme', isDark?'light':'dark'); location.reload();" class="p-2 -mr-1 rounded-lg text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="切换主题">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
    </div>
</div>

<!-- Mobile drawer overlay -->
<div x-show="drawerOpen" x-cloak x-transition.opacity
     @click="drawerOpen = false"
     class="fixed inset-0 bg-black/45 z-40 md:hidden"></div>

<!-- Mobile drawer -->
<aside x-show="drawerOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
       class="fixed top-0 left-0 h-full w-[280px] bg-white dark:bg-[#151c19] shadow-2xl z-50 flex flex-col md:hidden overflow-y-auto border-r border-[var(--calo-line)]">
    <div class="p-5 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-white text-sm font-bold" style="background: linear-gradient(135deg, #10b981, #047857);">C</span>
                <div>
                    <div class="text-lg font-bold tracking-tight">Calo</div>
                    <div class="text-xs text-[var(--calo-muted)]">热量管理，轻松减重</div>
                </div>
            </div>
        </div>
        <button type="button" @click="drawerOpen = false" class="p-1.5 rounded-lg text-[var(--calo-muted)] hover:bg-black/[0.04] dark:hover:bg-white/[0.05]" aria-label="关闭菜单">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 px-3 space-y-0.5 pb-4">
        <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[var(--calo-muted)]/80">记录</p>
        <a href="{{ route('dashboard') }}" class="{{ $navClass($isActive('dashboard')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            首页
        </a>
        <a href="{{ route('meals.index') }}" class="{{ $navClass($isActive('meals')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m4-10v12M8 8v8m8-6v10M4 10v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
            饮食记录
        </a>
        <a href="{{ route('exercises.index') }}" class="{{ $navClass($isActive('exercises')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            运动记录
        </a>
        <a href="{{ route('weights.index') }}" class="{{ $navClass($isActive('weights')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            体重记录
        </a>

        <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[var(--calo-muted)]/80">探索</p>
        <a href="{{ route('foods.index') }}" class="{{ $navClass($isActive('foods')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            食物库
        </a>
        <a href="{{ route('content.recipes') }}" class="{{ $navClass($isActive('content.recipe')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            食谱
        </a>
        <a href="{{ route('content.trainingPlans') }}" class="{{ $navClass($isActive('content.training')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            训练计划
        </a>
        <a href="{{ route('predictions.index') }}" class="{{ $navClass($isActive('predictions')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            趋势预测
        </a>
        <a href="{{ route('reports.history') }}" class="{{ $navClass($isActive('reports')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            报表
        </a>
        <a href="{{ route('exports.index') }}" class="{{ $navClass($isActive('exports')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            数据导出
        </a>
        <a href="{{ route('achievements.index') }}" class="{{ $navClass($isActive('achievements')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            成就
        </a>
    </nav>

    <div class="p-3 border-t border-[var(--calo-line)] space-y-0.5">
        <a href="{{ route('profile.edit') }}" class="{{ $navClass($isActive('profile')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            个人资料
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[var(--calo-muted)] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                退出登录
            </button>
        </form>
    </div>
</aside>
</div>

<!-- Desktop sidebar -->
<aside class="desktop-sidebar w-64 bg-white dark:bg-[#151c19] border-r border-[var(--calo-line)] fixed top-0 left-0 h-full z-30 overflow-y-auto" style="display:none;">
    <div class="p-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" aria-label="返回首页">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl text-white font-bold text-sm shadow-soft" style="background: linear-gradient(135deg, #10b981, #047857);">C</span>
            <div>
                <div class="text-lg font-bold tracking-tight text-[var(--calo-ink)]">Calo</div>
                <div class="text-[11px] text-[var(--calo-muted)]">热量管理，轻松减重</div>
            </div>
        </a>
    </div>

    <nav class="flex-1 px-3 space-y-0.5">
        <p class="px-3 pt-1 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[var(--calo-muted)]/80">记录</p>
        <a href="{{ route('dashboard') }}" class="{{ $navClass($isActive('dashboard')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            首页
        </a>
        <a href="{{ route('meals.index') }}" class="{{ $navClass($isActive('meals')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m4-10v12M8 8v8m8-6v10M4 10v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
            饮食记录
        </a>
        <a href="{{ route('exercises.index') }}" class="{{ $navClass($isActive('exercises')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            运动记录
        </a>
        <a href="{{ route('weights.index') }}" class="{{ $navClass($isActive('weights')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            体重记录
        </a>

        <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-[var(--calo-muted)]/80">探索</p>
        <a href="{{ route('foods.index') }}" class="{{ $navClass($isActive('foods')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            食物库
        </a>
        <a href="{{ route('content.recipes') }}" class="{{ $navClass($isActive('content.recipe')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            食谱
        </a>
        <a href="{{ route('content.trainingPlans') }}" class="{{ $navClass($isActive('content.training')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            训练计划
        </a>
        <a href="{{ route('predictions.index') }}" class="{{ $navClass($isActive('predictions')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            趋势预测
        </a>
        <a href="{{ route('reports.history') }}" class="{{ $navClass($isActive('reports')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            报表
        </a>
        <a href="{{ route('exports.index') }}" class="{{ $navClass($isActive('exports')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            数据导出
        </a>
        <a href="{{ route('achievements.index') }}" class="{{ $navClass($isActive('achievements')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            成就
        </a>
    </nav>

    <div class="p-3 border-t border-[var(--calo-line)] space-y-0.5">
        <a href="{{ route('profile.edit') }}" class="{{ $navClass($isActive('profile')) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            个人资料
        </a>
        <div class="flex items-center justify-between px-3 py-2">
            <span class="text-xs text-[var(--calo-muted)]">外观</span>
            <button type="button" onclick="const t=localStorage.getItem('theme'); const isDark=t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches); localStorage.setItem('theme', isDark?'light':'dark'); location.reload();" class="text-xs font-medium text-brand-700 dark:text-brand-400 hover:underline">切换主题</button>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[var(--calo-muted)] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                退出登录
            </button>
        </form>
    </div>
</aside>
</div>
