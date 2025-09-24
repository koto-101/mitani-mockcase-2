<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'clock_in' => null,
            'clock_out' => null,
            'note' => $this->faker->optional()->sentence(),
            'status' => '勤務外',
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
