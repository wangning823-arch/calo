<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(10)->format('Y-m-d'), 'after:1920-01-01'],
            'height' => ['required', 'numeric', 'min:30', 'max:250'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,heavy'],
            'unit_preference' => ['nullable', 'in:jin,kg'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '请填写昵称。',
            'gender.required' => '请选择性别。',
            'gender.in' => '请选择有效的性别。',
            'date_of_birth.required' => '请选择出生日期。',
            'date_of_birth.date' => '出生日期格式不正确。',
            'date_of_birth.before' => '年龄不能小于10岁。',
            'date_of_birth.after' => '出生日期不合理。',
            'height.required' => '请填写身高。',
            'height.min' => '身高不能低于30cm。',
            'height.max' => '身高不能超过250cm。',
            'activity_level.required' => '请选择活动水平。',
            'activity_level.in' => '请选择有效的活动水平。',
        ];
    }
}
