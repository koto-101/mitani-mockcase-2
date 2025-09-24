<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class AdminRequestDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを作成
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function testStampCorrectionRequestIsApprovedAndAttendanceIsUpdated()
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 出勤時刻と退勤時刻をCarbonインスタンスで設定
        $clockIn = Carbon::create(2025, 9, 23, 9, 0, 0, 'Asia/Tokyo'); // 2025年9月23日 09:00:00
        $clockOut = Carbon::create(2025, 9, 23, 18, 0, 0, 'Asia/Tokyo'); // 2025年9月23日 18:00:00

        // 出勤記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-23',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        // 修正申請を作成（承認待ち）
        $stampCorrectionRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending', // 承認待ち
            'reason' => 'The clock-in time is incorrect.', // 理由
        ]);

        // 管理者としてログインし、修正申請の詳細ページにアクセス
        $response = $this->actingAs($this->admin)->get("/admin/requests/{$stampCorrectionRequest->id}");

        $this->assertEquals('pending', $stampCorrectionRequest->status);
        // 承認ボタンを押す
        $response = $this->actingAs($this->admin)->post("/admin/requests/{$stampCorrectionRequest->id}/approve");

        // 修正申請が承認されたか
        $stampCorrectionRequest->refresh();
        $this->assertEquals('approved', $stampCorrectionRequest->status);

        // 勤怠情報が更新されているか（修正申請で変更した内容が反映されているか）
        $attendance->refresh();
        $this->assertEquals($attendance->status, 'approved'); // 勤怠情報のステータスが承認されたことを確認

        // 修正申請の理由が表示されているか
        $response->assertSee('The clock-in time is incorrect.');
    }
}
