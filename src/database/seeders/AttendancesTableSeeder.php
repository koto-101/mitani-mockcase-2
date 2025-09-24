<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = [1, 2];
        $startDate = Carbon::today()->subMonthsNoOverflow(3);
        $endDate = Carbon::today();
                
        foreach ($userIds as $userId) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // 平日のみデータ作成（土日除外）
                if (!$date->isWeekend()) {
                    $attendanceId = DB::table('attendances')->insertGetId([
                        'user_id' => $userId,
                        'date' => $date->toDateString(),
                        'clock_in' => $date->format('Y-m-d') . ' 09:00:00',
                        'clock_out' => $date->format('Y-m-d') . ' 18:00:00',
                        'note' => null,
                        'status' => 'present',
                        'recorded_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('break_logs')->insert([
                        'attendance_id' => $attendanceId,
                        'start_time' => $date->copy()->setTime(12, 0, 0),
                        'end_time' => $date->copy()->setTime(13, 0, 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $date->addDay();
            }
        }
    }
}
