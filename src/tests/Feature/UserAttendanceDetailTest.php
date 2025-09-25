<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $date;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
        ]);

        $this->date = Carbon::parse('2023-09-21');
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->date->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    /** @test */
    public function testUserNameIsDisplayedOnAttendanceDetailPage()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
    }

    /** @test */
    public function testSelectedDateIsDisplayedOnAttendanceDetailPage()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSeeText('2023年');
        $response->assertSeeText('9月21日');
    }

    /** @test */
    public function testStartAndEndTimeAreDisplayedCorrectly()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
    }

    /** @test */
    public function testBreakLogIsDisplayedCorrectly()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}
