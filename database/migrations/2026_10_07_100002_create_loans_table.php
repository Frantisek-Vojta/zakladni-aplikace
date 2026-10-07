<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            // cizi klice - vypujcka patri jedne knize a jednomu ctenari
            // cascadeOnDelete = kdyz smazu knihu, smazou se i jeji vypujcky
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reader_id')->constrained()->cascadeOnDelete();

            $table->date('borrowed_at');              // datum vypujceni
            $table->date('due_at');                   // datum, do kdy ma vratit
            $table->date('returned_at')->nullable();  // datum vraceni (null = jeste nevratil)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
