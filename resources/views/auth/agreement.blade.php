<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>用户协议与隐私政策 - Calo</title>
    @include('partials.app-scripts')
</head>
<body>
    @include("partials.sidebar")
    <div class="md:ml-64 min-h-screen pb-10 md:pb-8 page-shell flex flex-col">
        <div class="app-header sticky top-14 md:top-0 z-20">
            <div class="px-4 py-3 text-center">
                <h1 class="text-[15px] font-semibold">用户协议与隐私政策</h1>
            </div>
        </div>

        <div class="flex-1 px-4 py-5 md:px-8 max-w-3xl w-full mx-auto">
            <div class="card p-5 mb-4">
                <h2 class="mb-3 text-base font-semibold">用户协议</h2>
                <div class="space-y-2 text-sm text-[var(--calo-muted)] leading-relaxed">
                    <p>欢迎使用 Calo 热量管理应用。在使用本应用前，请仔细阅读以下条款：</p>
                    <p><strong class="text-[var(--calo-ink)]">1. 服务说明</strong>：Calo 为您提供饮食记录、热量计算、运动追踪等健康管理服务。</p>
                    <p><strong class="text-[var(--calo-ink)]">2. 账号安全</strong>：您应妥善保管账号密码，因账号泄露造成的损失由您自行承担。</p>
                    <p><strong class="text-[var(--calo-ink)]">3. 内容规范</strong>：您在应用中创建的内容应遵守法律法规。</p>
                    <p><strong class="text-[var(--calo-ink)]">4. 免责声明</strong>：本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。</p>
                </div>
            </div>

            <div class="card p-5 mb-4">
                <h2 class="mb-3 text-base font-semibold">隐私政策</h2>
                <div class="space-y-2 text-sm text-[var(--calo-muted)] leading-relaxed">
                    <p><strong class="text-[var(--calo-ink)]">1. 数据收集</strong>：我们收集您的基本档案信息（身高、体重、出生日期等）和使用记录。</p>
                    <p><strong class="text-[var(--calo-ink)]">2. 数据用途</strong>：您的数据仅用于提供热量计算和健康管理服务。</p>
                    <p><strong class="text-[var(--calo-ink)]">3. 健康数据处理</strong>：根据《个人信息保护法》要求，我们对您的健康数据进行单独同意处理。</p>
                    <p><strong class="text-[var(--calo-ink)]">4. 数据存储</strong>：您的数据存储在安全的服务器上，我们采取加密措施保护您的信息安全。</p>
                    <p><strong class="text-[var(--calo-ink)]">5. 数据删除</strong>：您可随时注销账号，我们将在 7 天后物理删除您的全部个人数据。</p>
                </div>
            </div>

            <label class="card flex cursor-pointer items-start gap-3 p-4">
                <input type="checkbox" id="agree-check" class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm leading-relaxed">
                    我已阅读并同意《用户协议》和《隐私政策》，并同意对我的健康数据进行处理。
                </span>
            </label>
        </div>

        <div class="mx-auto w-full max-w-3xl px-4 pb-8 md:px-8">
            <form method="POST" action="{{ route('agreement.accept') }}">
                @csrf
                <button type="submit" id="agree-btn" disabled class="btn-primary w-full py-3.5 text-[15px]">
                    同意并继续
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('agree-check').addEventListener('change', function() {
            document.getElementById('agree-btn').disabled = !this.checked;
        });
    </script>
</body>
</html>
