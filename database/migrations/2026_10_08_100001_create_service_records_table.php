<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABULKA SERVISNICH ZAZNAMU
 *
 * Jeden zaznam = jeden servisni zasah na jednom zarizeni.
 * Zarizeni jich muze mit libovolne mnozstvi (historie servisu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();

            // cizi klic na zarizeni.
            // Nazev tabulky si Laravel odvodi ze jmena sloupce, ale "equipment"
            // nema mnozne cislo, takze mu ji musime rict rucne.
            // cascadeOnDelete = se zarizenim zmizi i jeho servisni historie.
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();

            $table->string('description');   // co se delalo
            $table->date('serviced_at');     // kdy

            // decimal(10, 2) = cislo s dvema desetinnymi misty (koruny a halere).
            // Pro penize se NIKDY nepouziva float - ten pocita nepresne
            // (0.1 + 0.2 by nebylo presne 0.3).
            $table->decimal('cost', 10, 2);  // cena zasahu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
