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
        $user = User::factory()->create();
        $clockIn = Carbon::create(2025, 9, 23, 9, 0, 0, 'Asia/Tokyo');
        $clockOut = Carbon::create(2025, 9, 23, 18, 0, 0, 'Asia/Tokyo');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-23',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $stampCorrectionRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'The clock-in time is incorrect.',
        ]);

        $response = $this->actingAs($this->admin)
            ->followingRedirects()
            ->post("/admin/requests/{$stampCorrectionRequest->id}");

        // モデルを再取得してチェック
        $updatedRequest = StampCorrectionRequest::find($stampCorrectionRequest->id);
        $updatedAttendance = Attendance::find($attendance->id);

        $this->assertEquals('approved', $updatedRequest->status);
        $this->assertEquals('approved', $updatedAttendance->status);

        $response->assertSee('The clock-in time is incorrect.');
    }
}
