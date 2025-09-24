<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DateTimeDisplayTest extends TestCase
{
    /**
     * 現在の日時情報がUIと同じ形式で出力されていることをテスト
     */
    public function testDateTimeIsDisplayedInCorrectFormat()
    {
        // ログインユーザー作成
        $user = User::factory()->create();

        // ログイン状態でアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // Carbon を日本語ローカライズ
        \Carbon\Carbon::setLocale('ja_JP.UTF-8'); // Linux 環境で必要なことがある

        $now = \Carbon\Carbon::now();

        $expectedDate = $now->format('Y年n月j日'); // 例: 2025年9月21日
        $expectedWeekday = $now->isoFormat('dd'); // 例: 日

        $expectedTime = $now->format('H:i'); // 例: 15:05

        // アサーション
        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee("（{$expectedWeekday}）"); // 曜日カッコ付き
        $response->assertSee($expectedTime);
    }
}
