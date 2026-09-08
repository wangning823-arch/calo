<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>编辑运动记录 - Calo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @media (min-width: 768px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: flex !important; flex-direction: column; }
        }
        @media (max-width: 767px) {
            .mobile-bottom-nav { display: none !important; }
            .desktop-sidebar { display: none !important; }
        }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    @include("partials.sidebar")
    <div class="flex-1 md:ml-60 min-h-screen pb-20 md:pb-0">
    <div class="min-h-screen pb-20">
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold">编辑运动记录</h1>
                <div class="w-6"></div>
            </div>
        </div>

        @if($errors->any())
            <div class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('exercises.update', $record) }}" method="POST" class="px-4 mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">运动日期</label>
                        <input type="date" name="date" value="{{ old('date', $record->date->format('Y-m-d')) }}"
                               max="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">时间</label>
                        <input type="time" name="recorded_time" value="{{ old('recorded_time', $record->recorded_at ? $record->recorded_at->format('H:i') : '12:00') }}"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">运动项目</label>
                <select name="exercise_type_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                    @foreach($categories as $cat)
                        <optgroup label="{{ $cat }}">
                            @foreach($exerciseTypes->where('category', $cat) as $type)
                                <option value="{{ $type->id }}" {{ old('exercise_type_id', $record->exercise_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (MET {{ $type->met_value }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">运动时长（分钟）</label>
                <input type="number" name="duration_minutes" min="1" max="600"
                       value="{{ old('duration_minutes', $record->duration_minutes) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">运动强度</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['light' => '轻度', 'moderate' => '中度', 'heavy' => '高强度'] as $val => $label)
                        <label class="flex flex-col items-center p-3 border rounded-lg cursor-pointer transition-all"
                               style="{{ old('intensity', $record->intensity) === $val ? 'border-color: #2563eb; background: #eff6ff' : '' }}">
                            <input type="radio" name="intensity" value="{{ $val }}"
                                   {{ old('intensity', $record->intensity) === $val ? 'checked' : '' }} class="sr-only">
                            <div class="text-xs font-medium">{{ $label }}</div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">距离（km，可选）</label>
                <input type="number" name="distance_km" step="0.1" min="0" max="500"
                       value="{{ old('distance_km', $record->distance_km) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                保存修改
            </button>
        </form>

        <div class="px-4 mt-4">
            <form action="{{ route('exercises.destroy', $record) }}" method="POST" onsubmit="return confirm('确定要删除这条运动记录吗？')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-3 bg-red-50 text-red-600 rounded-lg font-medium hover:bg-red-100 transition-colors">
                    删除记录
                </button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
