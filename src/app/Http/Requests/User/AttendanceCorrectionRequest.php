<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'clock_in.date_format' => '出勤時間の形式が正しくありません',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考は文字列で入力してください',
            'reason.max' => '備考は255文字以内で入力してください',
            'breaks.*.start.date_format' => '休憩開始時間の形式が正しくありません',
            'breaks.*.end.date_format' => '休憩終了時間の形式が正しくありません',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // 出勤 > 退勤
            if ($clockIn && $clockOut && Carbon::parse($clockIn)->gt(Carbon::parse($clockOut))) {
                $validator->errors()->add('clock_in', '出勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                if ($start && $clockIn && Carbon::parse($start)->lt(Carbon::parse($clockIn))) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($start && $clockOut && Carbon::parse($start)->gt(Carbon::parse($clockOut))) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($end && $clockOut && Carbon::parse($end)->gt(Carbon::parse($clockOut))) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($start && $end && Carbon::parse($end)->lt(Carbon::parse($start))) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
