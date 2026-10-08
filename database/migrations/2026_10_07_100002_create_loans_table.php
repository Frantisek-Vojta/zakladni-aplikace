<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA VYPUJCEK
 *
 * Tady se propojuji knihy a ctenari. Vypujcka patri prave jedne knize
 * a prave jednomu ctenari, proto tu jsou dva cizi klice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            // CIZI KLICE
            //
            // foreignId('book_id')->constrained() udela dve veci:
            //   1) vytvori sloupec book_id
            //   2) nastavi vazbu na tabulku "books" - databaze pak nedovoli
            //      vlozit vypujcku s id knihy, ktera neexistuje
            // Nazev tabulky si Laravel odvodi ze jmena sloupce (book_id -> books).
            //
            // cascadeOnDelete() = kdyz se smaze kniha, smazou se i vsechny
            // jeji vypujcky. Bez toho by v tabulce zustaly "vypujcky niceho".
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reader_id')->constrained()->cascadeOnDelete();

            $table->date('borrowed_at');              // datum vypujceni
            $table->date('due_at');                   // datum, do kdy ma vratit

            // nullable() = sloupec smi byt prazdny (NULL).
            // Je to dulezite: prave podle toho se pozna nevracena kniha.
            $table->date('returned_at')->nullable();  // datum vraceni (null = jeste nevratil)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
