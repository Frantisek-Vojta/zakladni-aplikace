<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Testovaci data pro evidenci techniky.
     * Spusteni: php artisan db:seed --class=EquipmentSeeder
     *
     * Data jsou zamerne poskladana tak, aby byl videt kazdy stav
     * i zarizeni bez servisni historie.
     */
    public function run(): void
    {
        // --- v provozu, nekolik servisnich zasahu ---
        $notebook = Equipment::create([
            'name' => 'Notebook Dell Latitude',
            'inventory_number' => 'NB-001',
            'status' => 'active',
            'purchased_at' => '2024-03-15',
        ]);

        // zaznamy zakladame pres vazbu, equipment_id se vyplni samo
        $notebook->serviceRecords()->createMany([
            ['description' => 'Výměna baterie', 'serviced_at' => '2025-02-10', 'cost' => 2400.00],
            ['description' => 'Čištění chlazení', 'serviced_at' => '2025-09-01', 'cost' => 650.50],
        ]);

        // --- prave v servisu ---
        $vrtacka = Equipment::create([
            'name' => 'Vrtačka Bosch GSB 18V',
            'inventory_number' => 'VR-014',
            'status' => 'service',
            'purchased_at' => '2023-11-02',
        ]);

        $vrtacka->serviceRecords()->create([
            'description' => 'Oprava sklíčidla', 'serviced_at' => '2026-10-01', 'cost' => 1190.00,
        ]);

        // --- vyrazene zarizeni s nejdelsi historii ---
        $tiskarna = Equipment::create([
            'name' => 'Tiskárna HP LaserJet',
            'inventory_number' => 'TI-003',
            'status' => 'retired',
            'purchased_at' => '2019-06-20',
        ]);

        $tiskarna->serviceRecords()->createMany([
            ['description' => 'Výměna zapékací jednotky', 'serviced_at' => '2022-04-11', 'cost' => 3200.00],
            ['description' => 'Oprava podavače papíru', 'serviced_at' => '2023-08-23', 'cost' => 980.00],
            ['description' => 'Diagnostika před vyřazením', 'serviced_at' => '2025-12-05', 'cost' => 0.00],
        ]);

        // --- nove zarizeni, zadny servis (ukaze nulovy soucet) ---
        Equipment::create([
            'name' => 'Aku utahovák Makita',
            'inventory_number' => 'UT-021',
            'status' => 'active',
            'purchased_at' => '2026-09-28',
        ]);
    }
}
