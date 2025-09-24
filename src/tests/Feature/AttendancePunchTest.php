<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendancePunchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能することをテスト
     */
    public function testClockInButtonWorksCorrectly()
    {
        $user = User::factory()->create();

        // 出勤前の状態（ステータスなし）
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => null,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        // 出勤処理を実行
        $response = $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect('/attendance');

        // 出勤後、画面に「出勤中」が表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみできることをテスト
     */
    public function testCannotClockInTwiceInOneDay()
    {
        $user = User::factory()->create();

        // すでに退勤済みの勤怠を作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_out',
            'clock_in' => now()->startOfDay()->addHours(9),
            'clock_out' => now()->startOfDay()->addHours(18),
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('<button type="submit" name="action" value="clock_in">出勤</button>', false);
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できることをテスト
     */
    public function testClockInTimeIsVisibleInAttendanceList()
    {
        $user = User::factory()->create();

        // 出勤前の状態で勤怠を作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => null,
            'date' => now()->toDateString(),
        ]);

        // 出勤処理
        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        // 出勤時刻が表示されているかチェック（時:分単位でチェック）
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now())->first();
        $clockInTime = optional($attendance->clock_in)->format('H:i');

        $response->assertSee($clockInTime);
    }

        /**
     * 休憩入ボタンが正しく機能することをテスト
     */
    public function testBreakInButtonWorksCorrectly()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_in',
            'date' => now()->toDateString(),
        ]);

        // 出勤中に「休憩入」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩入の処理を実行
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_in',
        ]);

        // ステータスが「休憩中」になっていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできることをテスト（休憩入→休憩戻→再度休憩入）
     */
    public function testMultipleBreakInsInOneDayAreAllowed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_in',
            'date' => now()->toDateString(),
        ]);

        // 1回目の休憩入
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);

        // 休憩戻
        $this->actingAs($user)->post('/attendance', ['action' => 'break_out']);

        // もう一度休憩入
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);

        // 再度「休憩戻」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩戻ボタンが正しく機能することをテスト
     */
    public function testBreakOutButtonWorksCorrectly()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_in',
            'date' => now()->toDateString(),
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);

        // 休憩戻
        $this->actingAs($user)->post('/attendance', ['action' => 'break_out']);

        // ステータスが再び「出勤中」になることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできることをテスト
     */
    public function testMultipleBreakOutsInOneDayAreAllowed()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_in',
            'date' => now()->toDateString(),
        ]);

        // 1回目の休憩入
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);
        // 休憩戻
        $this->actingAs($user)->post('/attendance', ['action' => 'break_out']);
        // 2回目の休憩入
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);

        // 「休憩戻」ボタンが再び表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    function minutesToTime(int $minutes): string {
        return floor($minutes / 60) . ':' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 休憩時刻が勤怠一覧画面に表示されることをテスト
     */
    public function testBreakTimesAreShownInAttendanceList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
        ]);

        $break1 = $attendance->breakLogs()->create([
            'start_time' => now()->setTime(12, 0),
            'end_time' => now()->setTime(12, 30), // 30分
        ]);
        $break2 = $attendance->breakLogs()->create([
            'start_time' => now()->setTime(15, 0),
            'end_time' => now()->setTime(15, 15), // 15分
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 合計休憩時間は45分 (30 + 15)
        $totalBreakMinutes = 30 + 15;
        $expectedBreakTime = minutesToTime($totalBreakMinutes); // 0:45

        // 個別の休憩時刻ではなく、合計休憩時間が表示されていることを確認
        $response->assertSee($expectedBreakTime);
    }

    public function testClockOutButtonFunctionsCorrectly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
            'status' => 'clock_in',
        ]);

        // actingAsをチェーンしてリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 退勤ボタンのPOSTもチェーンで
        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);
        $response->assertRedirect('/attendance');

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function testCanConfirmClockOutTimeOnAttendanceListScreen()
    {
        $user = User::factory()->create();

        // まずは出勤中の勤怠レコードを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => null,
            'status' => 'clock_in',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'clock_out']);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $attendance = Attendance::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($attendance->clock_out);
        $response->assertSee($attendance->clock_out->format('H:i'));
    }
}
