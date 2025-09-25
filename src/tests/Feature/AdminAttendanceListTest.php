<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者がログインし、その日になされた全ユーザーの勤怠情報が正確に確認できること
     */
    public function testAdminCanViewAllUsersAttendanceForTheDay()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create();
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

        $date = '2025-09-22';

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);

        // 勤怠情報の表示確認（名前、時間表示）
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    /**
     * 勤怠一覧画面に遷移した際、現在の日付が表示されていること
     */
    public function testCurrentDateIsDisplayedOnAttendanceList()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $date = '2025-09-22';

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);

        $expectedTitle = \Carbon\Carbon::parse($date)->format('Y年n月j日');
        $response->assertSee($expectedTitle);

        $expectedNavDate = '📅' . \Carbon\Carbon::parse($date)->format('Y/m/d');
        $response->assertSee($expectedNavDate);
    }

    /**
     * 「前日」ボタンを押した際に前の日の勤怠情報が表示されること
     */
    public function testPreviousDayAttendanceIsDisplayedWhenPreviousButtonClicked()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $date = '2025-09-22';
        $prevDate = \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d');

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);

        // 前日リンクのhrefに正しい日付パラメータがあるかチェック
        $response->assertSee(route('admin.attendance.index', ['date' => $prevDate]));
    }

    /**
     * 「翌日」ボタンを押した際に次の日の勤怠情報が表示されること
     */
    public function testNextDayAttendanceIsDisplayedWhenNextButtonClicked()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $date = '2025-09-22';
        $nextDate = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d');

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);

        // 翌日リンクのhrefに正しい日付パラメータがあるかチェック
        $response->assertSee(route('admin.attendance.index', ['date' => $nextDate]));
    }
}
