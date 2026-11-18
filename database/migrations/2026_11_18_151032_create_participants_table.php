<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA UCASTNIKU
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // jmeno a prijmeni ucastnika

            // ZADANI: e-mail je jedinecny (platnost hlida validace v kontroleru,
            // databaze to overit neumi - jen zabrani dvema stejnym zaznamum)
            $table->string('email')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
