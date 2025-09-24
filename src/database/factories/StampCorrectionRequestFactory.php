<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => 1, 
            'user_id' => 1,       
            'status' => 'pending',
            'reason' => $this->faker->sentence(),
            'clock_in' => now(),
            'clock_out' => now()->addHours(8),
            'requested_at' => now(),
            'approved_at' => null,
        ];
    }
}
