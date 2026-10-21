<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\ServiceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy servisnich zaznamu: pridani z detailu, validace ceny a datumu,
 * soucet nakladu a export do CSV.
 */
class ServiceRecordTest extends TestCase
{
    use RefreshDatabase;

    private function equipment(array $data = []): Equipment
    {
        return Equipment::create(array_merge([
            'name' => 'Notebook Dell',
            'inventory_number' => 'NB-001',
            'status' => 'active',
            'purchased_at' => '2024-03-15',
        ], $data));
    }

    private function validData(array $data = []): array
    {
        return array_merge([
            'description' => 'Výměna baterie',
            'serviced_at' => '2025-02-10',
            'cost' => 2400.00,
        ], $data);
    }

    /**
     * ZADANI: pridani servisniho zaznamu z detailu zarizeni
     */
    public function test_service_record_can_be_added_from_equipment_detail(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData())
            ->assertRedirect(route('equipment.show', $item));

        $this->assertDatabaseHas('service_records', [
            'equipment_id' => $item->id,
            'description' => 'Výměna baterie',
        ]);
    }

    /**
     * ZADANI: detail servisni historie
     */
    public function test_equipment_detail_shows_service_history(): void
    {
        $item = $this->equipment();
        $this->post(route('service-records.store', $item), $this->validData());

        $this->get(route('equipment.show', $item))
            ->assertOk()
            ->assertSee('Notebook Dell')
            ->assertSee('Výměna baterie');
    }

    /**
     * ZADANI: soucet servisnich nakladu pro jedno zarizeni
     */
    public function test_total_service_cost_is_summed(): void
    {
        $item = $this->equipment();
        $item->serviceRecords()->createMany([
            $this->validData(['cost' => 2400.00]),
            $this->validData(['description' => 'Čištění', 'cost' => 650.50]),
        ]);

        $this->assertSame(3050.50, $item->totalServiceCost());

        // soucet se opravdu zobrazi (1234.5 -> "3 050,50")
        $this->get(route('equipment.show', $item))->assertSee('3 050,50');
    }

    public function test_total_service_cost_is_zero_without_records(): void
    {
        $this->assertSame(0.0, $this->equipment()->totalServiceCost());
    }

    /**
     * ZADANI: cena je nezaporne desetinne cislo
     */
    public function test_cost_cannot_be_negative(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['cost' => -100]))
            ->assertSessionHasErrors('cost');

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_cost_accepts_decimal_value(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['cost' => 650.50]))
            ->assertSessionHasNoErrors();

        $this->assertSame(650.50, $item->totalServiceCost());
    }

    public function test_cost_can_be_zero(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['cost' => 0]))
            ->assertSessionHasNoErrors();
    }

    public function test_cost_must_be_a_number(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['cost' => 'drahe']))
            ->assertSessionHasErrors('cost');
    }

    /**
     * ZADANI: datum servisu nesmi byt v nesmyslnem formatu
     */
    public function test_service_date_must_be_a_valid_date(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['serviced_at' => 'minuly tyden']))
            ->assertSessionHasErrors('serviced_at');

        $this->assertDatabaseCount('service_records', 0);
    }

    public function test_description_is_required(): void
    {
        $item = $this->equipment();

        $this->post(route('service-records.store', $item), $this->validData(['description' => '']))
            ->assertSessionHasErrors('description');
    }

    public function test_service_record_can_be_deleted(): void
    {
        $item = $this->equipment();
        $this->post(route('service-records.store', $item), $this->validData());
        $record = ServiceRecord::first();

        $this->delete(route('service-records.destroy', $record))
            ->assertRedirect(route('equipment.show', $item));

        $this->assertModelMissing($record);
    }

    public function test_deleting_equipment_deletes_its_service_records(): void
    {
        $item = $this->equipment();
        $this->post(route('service-records.store', $item), $this->validData());

        $this->delete(route('equipment.destroy', $item));

        $this->assertDatabaseCount('service_records', 0);
    }

    /**
     * Volitelne rozsireni ze zadani: export servisni historie do CSV
     */
    public function test_service_history_can_be_exported_to_csv(): void
    {
        $item = $this->equipment();
        $item->serviceRecords()->createMany([
            $this->validData(['cost' => 2400.00]),
            $this->validData(['description' => 'Čištění chlazení', 'cost' => 650.50]),
        ]);

        $response = $this->get(route('equipment.export', $item));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertDownload('servis-NB-001.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Výměna baterie', $csv);
        $this->assertStringContainsString('Čištění chlazení', $csv);
        $this->assertStringContainsString('CELKEM', $csv);
        $this->assertStringContainsString('3050.5', $csv);
    }
}
