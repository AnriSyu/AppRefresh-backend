<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeBlock;

class TimeBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daysOfWeek = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday'
        ];

        $hours = [
            '08:00 - 10:00',
            '10:00 - 12:00',
            '12:00 - 14:00',
            '14:00 - 16:00',
            '16:00 - 18:00'
        ];

        foreach ($daysOfWeek as $day) {
            foreach ($hours as $hour) {
                TimeBlock::create([
                    'day_of_week' => $day,
                    'hours' => $hour
                ]);
            }
        }
    }
}
