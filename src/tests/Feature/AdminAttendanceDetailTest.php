<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細ページが正しく表示されるか
     */

    public function testAttendanceDetailDisplaysSelectedAttendanceData()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);


        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
            'date' => '2025-09-22',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-22 12:00:00',
            'end_time' => '2025-09-22 13:00:00',
        ]);

        StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'reason' => 'Test note',
            'status' => 'pending',
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('Test note');
    }


    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testErrorShownWhenClockInAfterClockOut()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
            'date' => '2025-09-22',
        ]);

        $data = [
            'clock_in' => '2025-09-22 19:00:00', // 退勤時間より後
            'clock_out' => '2025-09-22 18:00:00',
            'reason' => 'Valid note',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.requestCorrection', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testErrorShownWhenBreakStartAfterClockOut()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
            'date' => '2025-09-22',
        ]);

        $breakLog = BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-22 19:00:00', // 退勤時間より後
            'end_time' => '2025-09-22 19:30:00',
        ]);

        $data = [
            'breaks' => [
                ['start' => '19:00', 'end' => '19:30']
            ],
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'Valid note',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.requestCorrection', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が不適切な値です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testErrorShownWhenBreakEndAfterClockOut()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
            'date' => '2025-09-22',
        ]);

        $breakLog = BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-22 12:00:00',
            'end_time' => '2025-09-22 19:00:00', // 退勤時間より後
        ]);

        $data = [
            'breaks' => [
                ['start' => '12:00', 'end' => '19:00']
            ],
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'Valid note',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.requestCorrection', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors(['breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     * 1. 管理者ユーザーにログインをする
     * 2. 勤怠詳細ページを開く
     * 3. 備考欄を未入力のまま保存処理をする
     */
    public function testErrorShownWhenNoteIsEmpty()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
            'note' => 'Initial note',
            'date' => '2025-09-22',
        ]);

        $data = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '', // 空
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.requestCorrection', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }
}
