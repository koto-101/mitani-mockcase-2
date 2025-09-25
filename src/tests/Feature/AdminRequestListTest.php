<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class AdminRequestListTest extends TestCase
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
     * 修正申請の詳細内容が正しく表示される
     */
    public function testCorrectionRequestDetailsAreDisplayedCorrectly()
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 出勤時刻と退勤時刻をCarbonインスタンスで設定
        $clockIn = Carbon::create(2025, 9, 23, 9, 0, 0); // 2025年9月23日 09:00:00
        $clockOut = Carbon::create(2025, 9, 23, 18, 0, 0); // 2025年9月23日 18:00:00

        // 出勤記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-23',
            'clock_in' => '2025-09-23 09:00:00',
            'clock_out' => '2025-09-23 18:00:00',
        ]);

        // 休憩ログを作成（必要な場合）
        $breakStart = Carbon::create(2025, 9, 23, 12, 0, 0); // 12:00
        $breakEnd = Carbon::create(2025, 9, 23, 13, 0, 0); // 13:00
        $attendance->breakLogs()->create([
            'start_time' => $breakStart,
            'end_time' => $breakEnd,
        ]);

        // 修正申請を作成
        $stampCorrectionRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending', // 承認待ち
            'reason' => 'The clock-in time is incorrect.',
        ]);

        // 管理者としてログインし、修正申請の詳細ページにアクセス
        $response = $this->actingAs($this->admin)->get("/admin/requests/{$stampCorrectionRequest->id}");

        // ステータスコードと表示内容を確認
        $response->assertStatus(200);

        // ユーザー名と日付が表示されているか
        $response->assertSee($user->name);
        $response->assertSee('2025年');
        $response->assertSee('9月23日');

        // 出勤・退勤時刻が期待通りに表示されているか
        $response->assertSeeText('09:00 ～ 18:00');

        // 休憩時間の表示を確認（休憩ログが1件だけなので "休憩" で表示される）
        $response->assertSee('休憩');
        $response->assertSee($breakStart->format('H:i') . ' 〜 ' . $breakEnd->format('H:i')); // 12:00 〜 13:00

        // 理由が表示されているか
        $response->assertSee('The clock-in time is incorrect.');
    }
}

