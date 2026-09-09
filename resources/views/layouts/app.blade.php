<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>@yield('title', 'Calo - 热量管理')</title>
    @include('partials.app-scripts')
</head>
<body>
    @hasSection('sidebar')
        @include('partials.sidebar')
    @endif

    <main class="@hasSection('sidebar') md:ml-64 min-h-screen pb-24 md:pb-10 @else min-h-screen @endif">
        @yield('content')
    </main>
</body>
</html>
