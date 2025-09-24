<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後に認証メールが送信されることをテスト
     */
    public function testEmailIsSentAfterRegistration(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    /**
     * 認証誘導画面から「認証はこちらから」ボタンを押下するとメール認証サイトに遷移することをテスト
     */
    public function testRedirectsToVerificationNoticePage()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから'); // ボタンテキストなどで調整可能
    }

    /**
     * メール認証完了後に勤怠登録画面に遷移することをテスト
     */
    public function testUserCanVerifyEmailAndRedirectedToAttendancePage()
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        // 署名付きURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // ログイン状態で署名付きURLにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // 勤怠登録画面（例：/attendance/punch）へのリダイレクトを想定
        $response->assertRedirect('/attendance');
    }
}
