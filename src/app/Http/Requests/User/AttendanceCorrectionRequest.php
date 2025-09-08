<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'note' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.after' => '退勤時間は出勤時間より後にしてください',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // 出勤・退勤の前後チェック
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                // 勤務時間外の休憩チェック（出勤より前／退勤より後）
                if (
                    ($clockIn && $start && $start < $clockIn) ||
                    ($clockOut && $start && $start > $clockOut) ||
                    ($clockOut && $end && $end > $clockOut)
                ) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                }

                // 休憩終了が休憩開始より前はNG
                if ($start && $end && $end < $start) {
                    $validator->errors()->add("breaks.$index.end", '休憩終了時間が開始時間より前です');
                }
            }
        });
    }
}
