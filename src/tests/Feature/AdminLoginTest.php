<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationMessageIsShownWhenEmailIsMissing()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'),
            'is_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function testValidationMessageIsShownWhenPasswordIsMissing()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'),
            'is_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function testValidationMessageIsShownWhenCredentialsDoNotMatch()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correctPassword'),
            'is_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrongadmin@example.com',
            'password' => 'correctPassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
