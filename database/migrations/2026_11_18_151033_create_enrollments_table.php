<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA PRIHLASEK
 *
 * Prihlaska je spojovaci tabulka mezi kurzem a ucastnikem - drzi informaci
 * "kdo se kdy na jaky kurz prihlasil a v jakem je to stavu".
 *
 * Na rozdil od vypujcky v knihovne (ktera stav pozna podle toho, jestli je
 * returned_at vyplnene) ma prihlaska vlastni sloupec "status" - zadani
 * vyzaduje tri stavy (enrolled, cancelled, completed), ktere nejdou
 * vyjadrit jen jednim datumem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // CIZI KLICE - prihlaska patri prave jednomu kurzu a prave
            // jednomu ucastnikovi. cascadeOnDelete() = kdyz se kurz nebo
            // ucastnik smaze, zmizi i jeho prihlasky (nezustanou "osirele").
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();

            $table->date('enrolled_at');        // datum prihlaseni

            // ZADANI (domaci prace): stavy enrolled / cancelled / completed.
            // Povolene hodnoty hlida Rule::in v EnrollmentController a jsou
            // vypsane v Enrollment::STATUSES - viz komentar tam.
            $table->string('status')->default('enrolled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
