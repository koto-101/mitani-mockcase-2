<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\BreakLog;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されていることをテスト
     */
    public function testAllMyAttendanceRecordsAreDisplayed()
    {
        $user = User::factory()->create();

        // 勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-01',
            'clock_in' => '2025-09-01 09:00:00',
            'clock_out' => '2025-09-01 18:00:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-01 12:00:00',
            'end_time' => '2025-09-01 13:00:00',
        ]);

        // 他のユーザーの勤怠情報（表示されないはず）
        Attendance::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        // 出勤・退勤・休憩時間が表示されているか
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00'); // 休憩時間（60分 → 1:00）

        // 日付＋曜日表示の確認（例: 09/01（月））
        $date = Carbon::parse('2025-09-01');
        $weekdayJP = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
        $formattedDate = $date->format('m/d') . '（' . $weekdayJP . '）';

        $response->assertSee($formattedDate);
    }


    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示されていることをテスト
     */
    public function testCurrentMonthIsDisplayedOnAttendanceListPage()
    {
        $user = User::factory()->create();

        // 現在日時を固定（例: 2025-09-21）
        $fixedNow = Carbon::parse('2025-09-21');
        Carbon::setTestNow($fixedNow);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        $currentMonth = $fixedNow->format('Y/m');
        $response->assertSee($currentMonth);

        // テスト後に日時を戻すのを忘れずに
        Carbon::setTestNow();
    }

    /**
     * 「前月」ボタンを押下した時に表示月の前月の情報が表示されていることをテスト
     */
    public function testPreviousMonthDataIsDisplayedWhenPreviousButtonClicked()
    {
        $user = User::factory()->create();

        // 現在日時を固定（例: 2025-09-21）
        $fixedNow = Carbon::parse('2025-09-21');
        Carbon::setTestNow($fixedNow);

        $previousMonth = $fixedNow->copy()->subMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonth->toDateString(),
            'clock_in' => $previousMonth->copy()->setTime(9, 0),
            'clock_out' => $previousMonth->copy()->setTime(18, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedNow->toDateString(),
            'clock_in' => $fixedNow->copy()->setTime(9, 0),
            'clock_out' => $fixedNow->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $previousMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y/m'));
        $weekdayJP = ['日', '月', '火', '水', '木', '金', '土'][$previousMonth->dayOfWeek];
        $response->assertSee($previousMonth->format('m/d') . '（' . $weekdayJP . '）');
        $response->assertDontSee($fixedNow->toDateString());

        Carbon::setTestNow(); // テスト後は解除
    }

    /**
     * 「翌月」ボタンを押下した時に表示月の翌月の情報が表示されていることをテスト
     */
    public function testNextMonthDataIsDisplayedWhenNextButtonClicked()
    {
        $user = User::factory()->create();

        // 現在日時を固定
        $fixedNow = Carbon::parse('2025-09-21');
        Carbon::setTestNow($fixedNow);

        $nextMonth = $fixedNow->copy()->addMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in' => $nextMonth->copy()->setTime(9, 0),
            'clock_out' => $nextMonth->copy()->setTime(18, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedNow->toDateString(),
            'clock_in' => $fixedNow->copy()->setTime(9, 0),
            'clock_out' => $fixedNow->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $nextMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$nextMonth->dayOfWeek];
        $response->assertSee($nextMonth->format('m/d'). '（' . $weekday . '）');
        $response->assertDontSee($fixedNow->toDateString());

        Carbon::setTestNow(); // 解除
    }

    /**
     * 「詳細」ボタンを押下するとその日の勤怠詳細画面に遷移することをテスト
     */
    public function testDetailPageIsShownWhenDetailButtonClicked()
    {
        $user = User::factory()->create();

        // 日時を固定
        $fixedNow = Carbon::parse('2025-09-21');
        Carbon::setTestNow($fixedNow);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedNow->toDateString(),
            'clock_in' => $fixedNow->copy()->setTime(9, 0),
            'clock_out' => $fixedNow->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        $detailUrl = route('attendance.detail', ['id' => $attendance->id]);

        $response->assertSee($detailUrl);

        $response = $this->actingAs($user)->get($detailUrl);
        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));

        // 解除
        Carbon::setTestNow();
    }

    private function getJapaneseWeekday(int $dayOfWeek): string
    {
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        return $weekdays[$dayOfWeek] ?? '';
    }
}
