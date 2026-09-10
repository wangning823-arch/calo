<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户协议 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">用户协议</h1>
        <div class="prose prose-sm space-y-4">
            <p>欢迎使用Calo热量管理应用（以下简称"本应用"）。</p>
            <h2 class="text-lg font-semibold">一、服务条款</h2>
            <p>本应用提供饮食记录、热量计算、运动追踪等健康管理服务。您在使用本应用时，应遵守本协议。</p>
            <h2 class="text-lg font-semibold">二、账号注册</h2>
            <p>您通过手机号注册本应用账号，应确保提供的手机号真实有效。</p>
            <h2 class="text-lg font-semibold">三、用户行为</h2>
            <p>您在使用本应用时，不得从事违反法律法规的行为。</p>
            <h2 class="text-lg font-semibold">四、免责声明</h2>
            <p>本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。如有特殊健康状况，请咨询医生后制定计划。</p>
            <h2 class="text-lg font-semibold">五、协议修改</h2>
            <p>我们有权根据需要修改本协议，修改后的协议将在应用内通知您。</p>
        </div>
        <div class="mt-8 text-xs text-[var(--calo-muted)]">最后更新：2026年9月8日</div>
    </div>
    </div>
</body>
</html>
