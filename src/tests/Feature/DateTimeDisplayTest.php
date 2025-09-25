<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;

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
        Carbon::setLocale('ja_JP.UTF-8');

        $now = Carbon::now();

        $expectedDate = $now->format('Y年n月j日'); 
        $expectedWeekday = $now->isoFormat('dd'); 

        $expectedTime = $now->format('H:i');

        // アサーション
        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee("（{$expectedWeekday}）");
        $response->assertSee($expectedTime);
    }
}
