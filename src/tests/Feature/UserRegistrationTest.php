<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力のとき、バリデーションエラーが出る
     */
    public function testErrorOccursIfNameIsNotEntered()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * メールアドレスが未入力のとき、バリデーションエラーが出る
     */
    public function testErrorOccursIfEmailIsNotEntered()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * パスワードが8文字未満だとエラーになる
     */
    public function testErrorOccursIfPasswordIsLessThan8Characters()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'abc123', // 6文字
            'password_confirmation' => 'abc123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * パスワードと確認用パスワードが一致しないとエラーになる
     */
    public function testErrorOccursIfPasswordConfirmationDoesNotMatch()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * パスワードが未入力だとエラーになる
     */
    public function testErrorOccursIfPasswordIsNotEntered()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * フォームが正しく入力されていれば登録成功する
     */
    public function testUserRegistrationSucceedsWithValidInformation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後、ログイン状態 or リダイレクト先など確認
        $response->assertRedirect('/email/verify'); // メール認証が有効な場合

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
