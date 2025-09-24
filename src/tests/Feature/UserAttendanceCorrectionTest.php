<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2023-09-21',
            'clock_in' => '2023-09-21 09:00:00',
            'clock_out' => '2023-09-21 18:00:00',
            'status' => 'approved',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '2023-09-21 12:00:00',
            'end_time' => '2023-09-21 13:00:00',
        ]);
    }

    /** @test */
    public function testClockInAfterClockOutShowsError()
    {
        $response = $this->actingAs($this->user)->post(route('requestCorrection', $this->attendance->id), [
            'clock_in' => '2023-09-21 19:00:00',
            'clock_out' => '2023-09-21 18:00:00',
            'breaks' => [],
            'reason' => 'Some reason',
        ]);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間が不適切な値です']);
    }

    /** @test */
    public function testBreakStartAfterClockOutShowsError()
    {
        $response = $this->actingAs($this->user)->post(route('requestCorrection', $this->attendance->id), [
            'clock_in' => '2023-09-21 09:00:00',
            'clock_out' => '2023-09-21 18:00:00',
            'breaks' => [
                ['start' => '19:00:00', 'end' => '20:00:00'],
            ],
            'reason' => 'Some reason',
        ]);

        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が不適切な値です']);
    }

    /** @test */
    public function testBreakEndAfterClockOutShowsError()
    {
        $response = $this->actingAs($this->user)->post(route('requestCorrection', $this->attendance->id), [
            'clock_in' => '2023-09-21 09:00:00',
            'clock_out' => '2023-09-21 18:00:00',
            'breaks' => [
                ['start' => '12:00:00', 'end' => '19:00:00'],
            ],
            'reason' => 'Some reason',
        ]);

        $response->assertSessionHasErrors(['breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function testEmptyReasonShowsError()
    {
        $response = $this->actingAs($this->user)->post(route('requestCorrection', $this->attendance->id), [
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'breaks' => [],
            'reason' => '',
        ]);
        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }

    /** @test */
    public function testCorrectionRequestIsCreatedSuccessfully()
    {
        $this->assertDatabaseMissing('stamp_correction_requests', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('requestCorrection', $this->attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00:00', 'end' => '13:00:00'],
            ],
            'reason' => 'Requesting correction',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'reason' => 'Requesting correction',
        ]);
    }

    /** @test */
    public function testAllPendingRequestsAreDisplayedForUser()
    {
        StampCorrectionRequest::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get(route('user.requests'));

        $response->assertStatus(200);

        StampCorrectionRequest::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->each(fn($request) => $response->assertSee($request->reason));
    }

    /** @test */
    public function testAllApprovedRequestsAreDisplayedForAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        StampCorrectionRequest::factory()->count(3)->create([
            'status' => 'approved',
            'user_id' => $admin->id,
            'attendance_id' => $this->attendance->id,
        ]);

        $response = $this->actingAs($admin)->get(route('requests.index', ['status' => 'approved']));

        $response->assertStatus(200);

        StampCorrectionRequest::where('status', 'approved')
            ->each(fn($request) => $response->assertSee((string)$request->id));
    }

    /** @test */
    public function testRequestDetailRedirectsToAttendanceDetail()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $correctionRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('requests.show', $correctionRequest->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.request_approval');
        $response->assertSee($correctionRequest->reason); 
    }
}
