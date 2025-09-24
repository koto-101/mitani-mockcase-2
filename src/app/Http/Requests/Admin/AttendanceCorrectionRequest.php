<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 管理者のみ呼び出される前提
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'reason.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            // 出勤・退勤時間の前後チェック
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                // 休憩開始が出勤前 or 退勤後
                if ($start && $clockIn && $start < $clockIn) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($start && $clockOut && $start > $clockOut) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                // 休憩終了が退勤後
                if ($end && $clockOut && $end > $clockOut) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }

                // 休憩終了が開始より前
                if ($start && $end && $end < $start) {
                    $validator->errors()->add("breaks.$index.end", '休憩終了時間が開始時間より前です');
                }
            }
        });
    }
}
