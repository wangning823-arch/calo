<x-filament::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button
                wire:click="import"
                :loading="$isProcessing"
                :disabled="$isProcessing"
            >
                {{ $isProcessing ? '导入中...' : '开始导入' }}
            </x-filament::button>
        </div>

        @if($imported > 0 || $skipped > 0 || $failed > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border">
                <h3 class="font-semibold mb-2">导入结果</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-success-600">{{ $imported }}</div>
                        <div class="text-sm text-gray-500">成功导入</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-warning-600">{{ $skipped }}</div>
                        <div class="text-sm text-gray-500">跳过(重复)</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-danger-600">{{ $failed }}</div>
                        <div class="text-sm text-gray-500">失败</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
