<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExerciseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'intensity' => ['nullable', 'in:light,moderate,heavy'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'date' => ['nullable', 'date', 'after_or_equal:' . now()->subDays(30)->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'exercise_type_id.required' => '请选择运动项目。',
            'exercise_type_id.exists' => '所选运动项目不存在。',
            'duration_minutes.required' => '请输入运动时长。',
            'duration_minutes.min' => '运动时长不能少于1分钟。',
            'duration_minutes.max' => '运动时长不能超过600分钟。',
            'date.after_or_equal' => '只能补录最近30天的记录。',
        ];
    }
}
