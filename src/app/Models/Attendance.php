<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'note',
        'status',
        'recorded_at',
    ];

    protected $dates = [
        'date',
        'clock_in',
        'clock_out',
        'recorded_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function breakLogs()
    {
        return $this->hasMany(BreakLog::class);
    }

    public function getTotalBreakTimeAttribute()
    {
        return $this->breakLogs->sum(function ($break) {
            if ($break->start_time && $break->end_time) {
                return $break->end_time->diffInSeconds($break->start_time);
            }
            return 0;
        });
    }

    // 実働時間（秒単位）
    public function getWorkingTimeAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $totalWorkSeconds = $this->clock_out->diffInSeconds($this->clock_in);
        $totalBreakSeconds = $this->total_break_time;

        return $totalWorkSeconds - $totalBreakSeconds;
    }
}
