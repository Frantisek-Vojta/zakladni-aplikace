<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // nazev knihy
            $table->string('author');                         // autor
            $table->string('isbn', 13)->unique();             // ISBN - musi byt jedinecne
            $table->unsignedInteger('available_copies');      // pocet dostupnych vytisku
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
