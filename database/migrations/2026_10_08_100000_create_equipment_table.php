<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA ZARIZENI (firemni narad)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // nazev zarizeni

            // evidencni cislo je jedinecne - unique() zajisti, ze databaze
            // sama nedovoli dve zarizeni se stejnym cislem
            $table->string('inventory_number')->unique();

            // stav zarizeni: active / service / retired.
            // V databazi je to obycejny text; ze povolene hodnoty jsou jen
            // tyto tri, hlida validace v kontroleru (seznam je v modelu).
            $table->string('status')->default('active');

            $table->date('purchased_at');                 // datum nakupu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
