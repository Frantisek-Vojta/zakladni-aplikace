<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vytvoření tabulky pro úkoly.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');   // název úkolu
            $table->timestamps();      // created_at, updated_at
        });
    }

    /**
     * Odstranění tabulky při rollbacku.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
