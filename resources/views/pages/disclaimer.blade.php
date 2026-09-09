<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>免责声明 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">免责声明</h1>
        <div class="prose prose-sm text-gray-700 space-y-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="font-semibold text-yellow-800">重要提示</p>
                <p class="text-yellow-700">本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。如有特殊健康状况，请咨询医生后制定计划。</p>
            </div>
            <h2 class="text-lg font-semibold">一、健康声明</h2>
            <p>本应用提供的热量计算、营养建议、运动方案仅供参考，不构成医疗建议。您在使用本应用前，应咨询专业医疗人员。</p>
            <h2 class="text-lg font-semibold">二、准确性声明</h2>
            <p>本应用中的食物热量数据来源于公开数据库，可能存在误差。建议以实际食物标签为准。</p>
            <h2 class="text-lg font-semibold">三、运动声明</h2>
            <p>运动消耗热量为估算值，实际消耗因人而异。请根据自身情况调整运动强度。</p>
            <h2 class="text-lg font-semibold">四、特殊人群</h2>
            <p>孕期、哺乳期、青少年、老年人及有慢性疾病的人群，使用本应用前请咨询医生。</p>
        </div>
        <div class="mt-8 text-xs text-gray-400">最后更新：2026年9月8日</div>
    </div>
    </div>
</body>
</html>
