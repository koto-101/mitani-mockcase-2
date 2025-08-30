<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 管理者であれば許可（適宜認可処理を行ってもよい）
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
            'reason.string' => '修正理由は文字列で入力してください',
            'reason.max' => '修正理由は255文字以内で入力してください',
        ];
    }
}