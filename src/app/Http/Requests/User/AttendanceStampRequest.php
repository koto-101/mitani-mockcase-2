<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStampRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'action' => 'required|in:clock_in,clock_out,break_in,break_out',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'ステータスを選択してください。',
            'action.in' => '不正なステータスが指定されました。',
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'ステータス',
        ];
    }
}
