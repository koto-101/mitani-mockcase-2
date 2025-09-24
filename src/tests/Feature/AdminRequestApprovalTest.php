<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRequestApprovalTest extends TestCase
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
     * 承認待ちの修正申請が全て表示される
     */
    public function testPendingRequestsAreDisplayed()
    {
        // ユーザーを複数作成
        $users = User::factory()->count(3)->create();

        // 各ユーザーに対して出勤記録を作成
        foreach ($users as $user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);

            // 各ユーザーに対して未承認の修正申請を作成
            StampCorrectionRequest::factory()->create([
                'attendance_id' => $attendance->id, // 出勤記録IDを関連付け
                'user_id' => $user->id,
                'status' => 'pending', // ステータスが承認待ち
            ]);
        }

        // 管理者としてログインし、修正申請一覧ページへアクセス
        $response = $this->actingAs($this->admin)->get('/admin/requests?status=pending');

        // ステータスコードと表示内容を確認
        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    /**
     * 承認済みの修正申請が全て表示される
     */
    public function testApprovedRequestsAreDisplayed()
    {
        // ユーザーを複数作成
        $users = User::factory()->count(3)->create();

        // 各ユーザーに対して出勤記録を作成
        foreach ($users as $user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);

            // 各ユーザーに対して承認済みの修正申請を作成
            StampCorrectionRequest::factory()->create([
                'attendance_id' => $attendance->id, // 出勤記録IDを関連付け
                'user_id' => $user->id,
                'status' => 'approved', // ステータスが承認済み
            ]);
        }

        // 管理者としてログインし、修正申請一覧ページへアクセス
        $response = $this->actingAs($this->admin)->get('/admin/requests?status=approved');

        // ステータスコードと表示内容を確認
        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }
}
