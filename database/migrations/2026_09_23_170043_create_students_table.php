<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');    // jmeno a prijmeni
            $table->string('class');   // trida, napr. 3.A
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
