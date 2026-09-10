<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICP备案信息 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">ICP备案信息</h1>
        <div class="card p-6">
            <div class="space-y-4">
                <div>
                    <span class="text-sm text-[var(--calo-muted)]">网站名称</span>
                    <div class="font-medium">Calo热量管理</div>
                </div>
                <div>
                    <span class="text-sm text-[var(--calo-muted)]">ICP备案号</span>
                    <div class="font-medium">京ICP备XXXXXXXX号-1</div>
                </div>
                <div>
                    <span class="text-sm text-[var(--calo-muted)]">备案状态</span>
                    <div class="font-medium text-green-600 dark:text-green-400">已备案</div>
                </div>
            </div>
        </div>
        <div class="mt-4 text-xs text-[var(--calo-muted)] text-center">
            <p>本应用已按照《中华人民共和国电信条例》及相关规定完成ICP备案。</p>
        </div>
    </div>
    </div>
</body>
</html>
