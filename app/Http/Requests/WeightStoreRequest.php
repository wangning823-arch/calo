<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeightStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight' => ['required', 'numeric', 'min:10', 'max:150'],
            'unit' => ['nullable', 'in:kg'],
            'date' => ['nullable', 'date', 'after_or_equal:' . now()->subDays(30)->toDateString()],
            'body_fat_percentage' => ['nullable', 'numeric', 'min:1', 'max:60'],
            'waist_cm' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'hip_cm' => ['nullable', 'numeric', 'min:30', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'weight.required' => '请输入体重。',
            'weight.min' => '请输入合理体重（10-150kg）。',
            'weight.max' => '请输入合理体重（10-150kg）。',
            'date.after_or_equal' => '只能补录最近30天的记录。',
        ];
    }
}
