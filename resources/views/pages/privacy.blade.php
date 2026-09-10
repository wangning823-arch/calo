<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隐私政策 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">隐私政策</h1>
        <div class="prose prose-sm space-y-4">
            <p>本隐私政策说明了Calo如何收集、使用和保护您的个人信息。</p>
            <h2 class="text-lg font-semibold">一、信息收集</h2>
            <p>我们收集以下信息：手机号、姓名、性别、出生日期、身高、体重、饮食记录、运动记录。</p>
            <h2 class="text-lg font-semibold">二、健康数据处理</h2>
            <p>根据《个人信息保护法》要求，我们对您的健康数据进行单独同意处理，仅用于提供热量计算和健康管理服务。</p>
            <h2 class="text-lg font-semibold">三、信息存储与安全</h2>
            <p>您的数据存储在中国境内的安全服务器上，我们采用加密技术保护您的信息安全。</p>
            <h2 class="text-lg font-semibold">四、信息共享</h2>
            <p>未经您的同意，我们不会向第三方共享您的个人信息。</p>
            <h2 class="text-lg font-semibold">五、数据删除</h2>
            <p>您可随时注销账号，我们将在7天冷静期后物理删除您的全部个人数据。</p>
            <h2 class="text-lg font-semibold">六、数据可携权</h2>
            <p>根据PIPL，您有权获取您的个人数据副本，我们提供JSON/CSV格式的导出功能。</p>
        </div>
        <div class="mt-8 text-xs text-[var(--calo-muted)]">最后更新：2026年9月8日</div>
    </div>
    </div>
</body>
</html>
