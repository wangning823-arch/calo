<!DOCTYPE html>
<html lang="zh-CN" x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>用户协议与隐私政策 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; }
        }
        @media (max-width: 767px) {
            .desktop-sidebar { display: none !important; }
        }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen flex flex-col">
        <div class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 text-center">
                <h1 class="text-lg font-semibold dark:text-white">用户协议与隐私政策</h1>
            </div>
        </div>

        <div class="flex-1 px-4 py-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <h2 class="text-base font-semibold mb-2 dark:text-white">用户协议</h2>
                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>欢迎使用Calo热量管理应用。在使用本应用前，请仔细阅读以下条款：</p>
                    <p><strong>1. 服务说明</strong>：Calo为您提供饮食记录、热量计算、运动追踪等健康管理服务。</p>
                    <p><strong>2. 账号安全</strong>：您应妥善保管账号密码，因账号泄露造成的损失由您自行承担。</p>
                    <p><strong>3. 内容规范</strong>：您在应用中创建的内容应遵守法律法规。</p>
                    <p><strong>4. 免责声明</strong>：本应用提供的热量建议、食谱、训练计划均为一般健康信息而非医疗建议。</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
                <h2 class="text-base font-semibold mb-2 dark:text-white">隐私政策</h2>
                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p><strong>1. 数据收集</strong>：我们收集您的基本档案信息（身高、体重、出生日期等）和使用记录。</p>
                    <p><strong>2. 数据用途</strong>：您的数据仅用于提供热量计算和健康管理服务。</p>
                    <p><strong>3. 健康数据处理</strong>：根据《个人信息保护法》要求，我们对您的健康数据进行单独同意处理。</p>
                    <p><strong>4. 数据存储</strong>：您的数据存储在安全的服务器上，我们采取加密措施保护您的信息安全。</p>
                    <p><strong>5. 数据删除</strong>：您可随时注销账号，我们将在7天后物理删除您的全部个人数据。</p>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 mb-4">
                <label class="flex items-start cursor-pointer">
                    <input type="checkbox" id="agree-check" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-blue-800 dark:text-blue-300">
                        我已阅读并同意《用户协议》和《隐私政策》，并同意对我的健康数据进行处理。
                    </span>
                </label>
            </div>
        </div>

        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4" style="max-width: 430px; margin: 0 auto;">
            <form method="POST" action="{{ route('agreement.accept') }}">
                @csrf
                <button type="submit" id="agree-btn" disabled class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
    </div>
</body>
</html>
