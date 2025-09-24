<?php

namespace Database\Factories;

use App\Models\BreakLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakLogFactory extends Factory
{
    protected $model = BreakLog::class;

    public function definition()
    {
        return [
            'start_time' => now()->setTime(12, 0),
            'end_time'   => now()->setTime(13, 0),
        ];
    }
}