<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Nahraje ukazkova data: kurzy, ucastniky a par prihlasek
     * ve vsech trech stavech (enrolled, cancelled, completed).
     * Spusteni: php artisan db:seed --class=CourseSeeder
     */
    public function run(): void
    {
        // --- kurzy ---
        $php = Course::create([
            'name' => 'Základy PHP',
            'code' => 'PHP-101',
            'capacity' => 3,
            'starts_at' => now()->addMonth(),
        ]);

        $laravel = Course::create([
            'name' => 'Laravel pro začátečníky',
            'code' => 'LAR-201',
            'capacity' => 2,
            // kurz uz probehl - bude mit dokoncene prihlasky
            'starts_at' => now()->subMonth(),
        ]);

        Course::create([
            'name' => 'Pokročilý JavaScript',
            'code' => 'JS-301',
            'capacity' => 15,
            'starts_at' => now()->addMonths(3),
        ]);

        // --- ucastnici ---
        $jan = Participant::create(['name' => 'Jan Novák', 'email' => 'jan.novak@example.com']);
        $eva = Participant::create(['name' => 'Eva Malá', 'email' => 'eva.mala@example.com']);
        $petr = Participant::create(['name' => 'Petr Svoboda', 'email' => 'petr.svoboda@example.com']);

        // --- prihlasky ---
        // 1) bezna aktivni prihlaska
        Enrollment::create([
            'course_id' => $php->id,
            'participant_id' => $jan->id,
            'enrolled_at' => now()->subDays(3),
            'status' => Enrollment::STATUS_ENROLLED,
        ]);

        // 2) zrusena prihlaska - neuvazuje se do obsazenosti kurzu
        Enrollment::create([
            'course_id' => $php->id,
            'participant_id' => $eva->id,
            'enrolled_at' => now()->subDays(10),
            'status' => Enrollment::STATUS_CANCELLED,
        ]);

        // 3) kurz Laravel uz probehl -> dokoncene prihlasky
        Enrollment::create([
            'course_id' => $laravel->id,
            'participant_id' => $petr->id,
            'enrolled_at' => now()->subMonths(2),
            'status' => Enrollment::STATUS_COMPLETED,
        ]);

        Enrollment::create([
            'course_id' => $laravel->id,
            'participant_id' => $jan->id,
            'enrolled_at' => now()->subMonths(2),
            'status' => Enrollment::STATUS_COMPLETED,
        ]);
    }
}
