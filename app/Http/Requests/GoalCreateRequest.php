<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_weight' => ['required', 'numeric', 'min:30', 'max:300'],
            'target_date' => ['required', 'date', 'after:today'],
            'confirm_warning' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_weight.required' => '请输入目标体重。',
            'target_weight.min' => '目标体重不能低于30kg。',
            'target_weight.max' => '目标体重不能超过300kg。',
            'target_date.required' => '请选择目标日期。',
            'target_date.after' => '目标日期必须是未来。',
        ];
    }
}
