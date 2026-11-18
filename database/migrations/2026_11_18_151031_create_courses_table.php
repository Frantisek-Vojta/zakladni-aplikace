<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA KURZU
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // nazev kurzu

            // ZADANI: kod kurzu je jedinecny.
            // unique() je DRUHA pojistka (ta prvni je validace v kontroleru,
            // ktera ukaze hezkou chybu) - hlida data i kdyby se zaznam
            // zkousel vlozit jinudy (napr. ze seederu).
            $table->string('code')->unique();          // kratky kod kurzu, napr. "PHP-101"

            // ZADANI: kapacita je cele cislo vetsi nez 0.
            // unsignedInteger = nikdy zaporne cislo. Samotne "vetsi nez 0"
            // uz databaze neumi - to hlida validace v CourseController::rules().
            $table->unsignedInteger('capacity');        // maximalni pocet aktivnich prihlasek

            $table->date('starts_at');                  // datum zacatku kurzu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
