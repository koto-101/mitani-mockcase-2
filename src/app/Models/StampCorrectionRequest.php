<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'reason', 
        'clock_in',
        'clock_out',
        'requested_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function correctionBreakLogs()
    {
        return $this->hasMany(CorrectionBreakLog::class);
    }

    public function getNoteAttribute()
    {
        return $this->reason;
    }
}
