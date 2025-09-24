<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる
     */
    public function testAdminCanSeeAllUsersNameAndEmail()
    {
        // 管理者ユーザーを作成してログイン
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // 一般ユーザーを複数作成
        $users = User::factory()->count(3)->create(['is_admin' => false]);

        // スタッフ一覧ページへアクセス
        $response = $this->get('/admin/staff/list');

        // 各ユーザーの氏名とメールアドレスが表示されていることを確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }

        $response->assertStatus(200);
    }

    /**
     * 2.ユーザーの勤怠情報が正しく表示される
     */
    public function testUserAttendanceListIsDisplayedCorrectly()
    {
        // 管理者ユーザー作成・ログイン
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        // 一般ユーザーと勤怠データ作成
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-01 12:00:00',
            'end_time' => '2025-09-01 13:00:00',
        ]);

        // ユーザーの勤怠一覧ページへアクセス
        $response = $this->get("/admin/attendance/staff/{$user->id}");

        // 勤怠情報の日時が正しく表示されていること
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertStatus(200);
    }

    /**
     * 「前月」ボタンを押下すると前月の勤怠情報が表示される
     */
    public function testClickingPreviousMonthDisplaysPreviousMonthAttendances()
    {
        Carbon::setTestNow('2025-09-01');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create(['is_admin' => false]);

        // 今月の勤怠（表示されないはず）
        $attendance =Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-15',
            'clock_in' => '10:00',
            'clock_out' => '18:00',
        ]);

        // 前月の勤怠（表示されるはず）
        $attendance =Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-08-20',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-08-20 12:00:00',
            'end_time' => '2025-08-20 13:00:00',
        ]);

        // 勤怠一覧ページで前月のパラメータを付けてアクセス
        $response = $this->get(route('admin.attendance.staff', ['id' => $user->id, 'month' => '2025-08']));

        $response->assertSeeText('8:00', false); // '08:00' はあればいいのでfalseで厳密にエスケープしない

        // 今月の勤怠時間は含まれないことを期待
        $response->assertDontSee('10:00', false);

        $response->assertStatus(200);
    }

    /**
     * 「翌月」ボタンを押下すると翌月の勤怠情報が表示される
     */
    public function testClickingNextMonthDisplaysNextMonthAttendances()
    {
        Carbon::setTestNow('2025-09-01');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create(['is_admin' => false]);

        // 今月の勤怠（表示されないはず）
        $attendance =Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-09-15',
            'clock_in' => '2025-09-15 10:00:00',
            'clock_out' => '2025-09-15 18:00:00',
        ]);

        // 翌月の勤怠（表示されるはず）
        $attendance =Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-10-10',
            'clock_in' => '2025-10-10 09:00:00',
            'clock_out' => '2025-10-10 18:00:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-10-10 12:00:00',
            'end_time' => '2025-10-10 13:00:00',
        ]);

        // 勤怠一覧ページで翌月のパラメータを付けてアクセス
        $response = $this->get(route('admin.attendance.staff', ['id' => $user->id, 'month' => '2025-10']));

        $response->assertSee('09:00', false);

        // 今月の勤怠時間は含まれないことを期待
        $response->assertDontSee('10:00', false);

        $response->assertStatus(200);
    }

    /**
     * 「詳細」ボタンを押下すると勤怠詳細画面に遷移する
     */
    public function testClickingDetailButtonNavigatesToAttendanceDetailPage()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user = User::factory()->create(['is_admin' => false]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-22 09:00:00',
            'clock_out' => '2025-09-22 18:00:00',
        ]);

        // 勤怠詳細ページへのURLを取得してアクセス
        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細'); // ページ内に「勤怠詳細」という文字があること

        // 追加で勤怠時間表示を確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
