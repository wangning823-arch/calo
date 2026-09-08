<div x-data="healthAlert()" x-init="checkAlert()" x-show="showAlert" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-6" style="max-width: 430px; margin: 0 auto;">
    <div class="absolute inset-0 bg-black/60" @click="level !== 'critical' && (showAlert = false)"></div>
    <div class="relative bg-white rounded-2xl p-6 w-full max-w-sm">
        <div class="text-center mb-4">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-3"
                 :class="level === 'critical' ? 'bg-red-100' : 'bg-yellow-100'">
                <span class="text-2xl">⚠️</span>
            </div>
            <h3 class="text-lg font-semibold">健康提醒</h3>
        </div>

        <p class="text-sm text-gray-600 text-center mb-4" x-text="alertMessage"></p>

        <template x-if="level === 'critical'">
            <div class="space-y-2">
                <button @click="confirmAlert('adjust')"
                        class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium">
                    调整热量目标
                </button>
                <button @click="confirmAlert('acknowledge')"
                        class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-medium">
                    我已知晓风险并坚持当前方案
                </button>
            </div>
        </template>

        <template x-if="level !== 'critical'">
            <button @click="showAlert = false"
                    class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium">
                我知道了
            </button>
        </template>
    </div>
</div>

<script>
function healthAlert() {
    return {
        showAlert: false,
        level: null,
        alertMessage: '',

        async checkAlert() {
            try {
                const response = await fetch('{{ route("api.healthAlerts.check") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.has_alert) {
                    this.level = data.level;
                    this.alertMessage = data.alert?.message || '';
                    this.showAlert = true;
                }
            } catch (e) {}
        },

        async confirmAlert(choice) {
            await fetch('{{ route("api.healthAlerts.confirm") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ choice })
            });
            this.showAlert = false;
        }
    }
}
</script>
