<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function testValidationMessageIsShownWhenEmailIsMissing()
    {
        // ユーザーを作成
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // パスワードのみ送信（メールアドレスは未入力）
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function testValidationMessageIsShownWhenPasswordIsMissing()
    {
        // ユーザーを作成
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // メールアドレスのみ送信（パスワードは未入力）
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function testValidationMessageIsShownWhenCredentialsDoNotMatch()
    {
        // 正しいユーザーを登録
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctPassword'),
        ]);

        // 誤ったメールアドレスでログイン試行
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'correctPassword',
        ]);

        // エラーメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
