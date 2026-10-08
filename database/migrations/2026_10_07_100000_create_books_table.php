<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACE = predpis, jak ma vypadat tabulka v databazi.
 *
 * Migrace se spousti prikazem "php artisan migrate". Vyhoda proti rucnimu
 * klikani v databazi: predpis je v projektu, takze si stejnou databazi
 * vyrobi kdokoliv jednim prikazem.
 *
 * up()   = co se ma vytvorit
 * down() = jak to vratit zpet (pouzije se pri "php artisan migrate:rollback")
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();                                     // automaticky cislovany primarni klic
            $table->string('title');                          // nazev knihy
            $table->string('author');                         // autor

            // ISBN ma presne 13 znaku -> string('isbn', 13)
            // unique() = databaze sama nedovoli dve knihy se stejnym ISBN.
            // Pozor, tohle je DRUHA pojistka. Prvni je validace v kontroleru,
            // ktera uzivateli ukaze hezkou chybu. Tahle hlida data i kdyby
            // se zaznam zkousel vlozit jinudy (napr. ze seederu).
            $table->string('isbn', 13)->unique();

            // unsignedInteger = cele cislo bez znamenka, tedy nikdy zaporne.
            // Zadani chce "nejmene 0" - proto unsigned a ne obycejny integer.
            $table->unsignedInteger('available_copies');      // pocet dostupnych vytisku
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
