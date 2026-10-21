<?php

namespace Tests\Feature;

use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy zarizeni: CRUD, filtr podle stavu a validacni pravidla ze zadani.
 */
class EquipmentTest extends TestCase
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

    /**
     * Platna data pro formular
     */
    private function validData(array $data = []): array
    {
        return array_merge([
            'name' => 'Vrtačka Bosch',
            'inventory_number' => 'VR-014',
            'status' => 'active',
            'purchased_at' => '2024-05-01',
        ], $data);
    }

    public function test_equipment_is_listed(): void
    {
        $this->equipment();

        $this->get(route('equipment.index'))->assertOk()->assertSee('Notebook Dell')->assertSee('NB-001');
    }

    public function test_equipment_can_be_created(): void
    {
        $this->post(route('equipment.store'), $this->validData())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('equipment', ['inventory_number' => 'VR-014', 'status' => 'active']);
    }

    public function test_equipment_can_be_updated(): void
    {
        $item = $this->equipment();

        $this->put(route('equipment.update', $item), $this->validData([
            'name' => 'Notebook Dell Latitude',
            'inventory_number' => $item->inventory_number,   // vlastni cislo musi projit
            'status' => 'service',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('equipment', [
            'id' => $item->id, 'name' => 'Notebook Dell Latitude', 'status' => 'service',
        ]);
    }

    public function test_equipment_can_be_deleted(): void
    {
        $item = $this->equipment();

        $this->delete(route('equipment.destroy', $item))->assertRedirect(route('equipment.index'));

        $this->assertModelMissing($item);
    }

    /**
     * ZADANI: filtr podle stavu active / service / retired
     */
    public function test_equipment_can_be_filtered_by_status(): void
    {
        $this->equipment(['name' => 'V provozu kus', 'inventory_number' => 'A-1', 'status' => 'active']);
        $this->equipment(['name' => 'V servisu kus', 'inventory_number' => 'B-2', 'status' => 'service']);
        $this->equipment(['name' => 'Vyrazeny kus', 'inventory_number' => 'C-3', 'status' => 'retired']);

        $this->get(route('equipment.index', ['status' => 'active']))
            ->assertSee('V provozu kus')->assertDontSee('V servisu kus')->assertDontSee('Vyrazeny kus');

        $this->get(route('equipment.index', ['status' => 'service']))
            ->assertSee('V servisu kus')->assertDontSee('V provozu kus');

        $this->get(route('equipment.index', ['status' => 'retired']))
            ->assertSee('Vyrazeny kus')->assertDontSee('V provozu kus');

        // bez filtru se vypisou vsechna
        $this->get(route('equipment.index'))
            ->assertSee('V provozu kus')->assertSee('V servisu kus')->assertSee('Vyrazeny kus');
    }

    /**
     * ZADANI: evidencni cislo je jedinecne
     */
    public function test_inventory_number_must_be_unique(): void
    {
        $this->equipment(['inventory_number' => 'NB-001']);

        $this->post(route('equipment.store'), $this->validData(['inventory_number' => 'NB-001']))
            ->assertSessionHasErrors('inventory_number');

        $this->assertDatabaseCount('equipment', 1);
    }

    /**
     * ZADANI: status patri do povoleneho seznamu
     */
    public function test_status_must_be_from_allowed_list(): void
    {
        $this->post(route('equipment.store'), $this->validData(['status' => 'rozbite']))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('equipment', 0);
    }

    public function test_all_allowed_statuses_are_accepted(): void
    {
        foreach (array_keys(Equipment::STATUSES) as $i => $status) {
            $this->post(route('equipment.store'), $this->validData([
                'inventory_number' => "INV-{$i}",
                'status' => $status,
            ]))->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('equipment', count(Equipment::STATUSES));
    }

    /**
     * ZADANI: datum nakupu nesmi byt v nesmyslnem formatu
     */
    public function test_purchase_date_must_be_a_valid_date(): void
    {
        $this->post(route('equipment.store'), $this->validData(['purchased_at' => 'vcera']))
            ->assertSessionHasErrors('purchased_at');

        $this->post(route('equipment.store'), $this->validData(['purchased_at' => '2024-13-45']))
            ->assertSessionHasErrors('purchased_at');

        $this->assertDatabaseCount('equipment', 0);
    }

    public function test_name_and_inventory_number_are_required(): void
    {
        $this->post(route('equipment.store'), [])->assertSessionHasErrors(['name', 'inventory_number']);
    }

    /**
     * ZADANI (bod 4): stav je rozlisen barevne i textove
     */
    public function test_status_is_shown_with_label_and_colour(): void
    {
        $item = $this->equipment(['status' => 'service']);

        // textovy popis
        $this->assertSame('V servisu', $item->statusLabel());
        // CSS trida pro barvu
        $this->assertSame('status-service', $item->statusClass());

        // oboji se opravdu dostane do stranky
        $this->get(route('equipment.show', $item))->assertSee('V servisu')->assertSee('status-service');
    }
}
