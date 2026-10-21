<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * RESOURCE CONTROLLER ZARIZENI
 *
 * "Resource controller" = kontroler se sedmi standardnimi metodami, ktere
 * Laravel umi napojit jedinym radkem v routes/web.php:
 *     Route::resource('equipment', EquipmentController::class);
 *
 *   index   GET    /equipment                seznam
 *   create  GET    /equipment/create         formular pro nove
 *   store   POST   /equipment                ulozeni noveho
 *   show    GET    /equipment/{equipment}    detail
 *   edit    GET    /equipment/{equipment}/edit  formular pro upravu
 *   update  PUT    /equipment/{equipment}    ulozeni upravy
 *   destroy DELETE /equipment/{equipment}    smazani
 */
class EquipmentController extends Controller
{
    /**
     * Seznam zarizeni + filtr podle stavu (active / service / retired)
     */
    public function index(Request $request): View
    {
        // z adresy /equipment?status=service vytahneme pozadovany stav
        $status = $request->query('status');

        $equipment = Equipment::query()
            // Filtrujeme jen kdyz je stav vyplneny A je to jeden z povolenych.
            // Druha podminka brani tomu, aby si nekdo rucne v adrese vymyslel
            // neexistujici stav a dostal prazdny seznam bez vysvetleni.
            ->when($status && array_key_exists($status, Equipment::STATUSES),
                fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->get();

        return view('equipment.index', [
            'equipment' => $equipment,
            'status' => $status,
            // seznam stavu posilame do sablony na vykresleni filtracnich odkazu
            'statuses' => Equipment::STATUSES,
        ]);
    }

    /**
     * Formular pro nove zarizeni
     */
    public function create(): View
    {
        return view('equipment.create', ['statuses' => Equipment::STATUSES]);
    }

    /**
     * Ulozi nove zarizeni
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $equipment = Equipment::create($data);

        // po ulozeni presmerujeme na detail, aby uzivatel hned mohl
        // pridat servisni zaznam
        return redirect()->route('equipment.show', $equipment);
    }

    /**
     * DETAIL + SERVISNI HISTORIE + SOUCET NAKLADU
     *
     * Equipment $equipment = route model binding: z adresy /equipment/3
     * si Laravel sam najde zarizeni s id 3 (a vrati 404 kdyz neexistuje).
     */
    public function show(Equipment $equipment): View
    {
        return view('equipment.show', [
            'equipment' => $equipment,
            // nejnovejsi servis nahore
            'records' => $equipment->serviceRecords()->orderByDesc('serviced_at')->get(),
            // soucet servisnich nakladu (metoda je v modelu Equipment)
            'totalCost' => $equipment->totalServiceCost(),
        ]);
    }

    /**
     * Formular pro upravu
     */
    public function edit(Equipment $equipment): View
    {
        return view('equipment.edit', [
            'equipment' => $equipment,
            'statuses' => Equipment::STATUSES,
        ]);
    }

    /**
     * Ulozi upravu
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        // zarizeni predame do rules(), aby si pri kontrole jedinecnosti
        // evidencniho cisla "nestezovalo samo na sebe"
        $data = $request->validate($this->rules($equipment));

        $equipment->update($data);

        return redirect()->route('equipment.show', $equipment);
    }

    /**
     * Smaze zarizeni.
     * Servisni zaznamy zmizi samy diky cascadeOnDelete() v migraci.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        $equipment->delete();

        return redirect()->route('equipment.index');
    }

    /**
     * VALIDACNI PRAVIDLA PRO ZARIZENI
     *
     * V jedne metode, aby se neopakovala ve store() i update().
     * $equipment je vyplnene jen pri uprave, jinak null.
     */
    private function rules(?Equipment $equipment = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // ZADANI: evidencni cislo je jedinecne.
            // ->ignore($equipment) pri uprave vynecha zarizeni samotne -
            // bez toho by jeho vlastni cislo bylo oznaceno za duplikat.
            'inventory_number' => [
                'required', 'string', 'max:50',
                Rule::unique('equipment', 'inventory_number')->ignore($equipment),
            ],

            // ZADANI: status patri do povoleneho seznamu.
            // array_keys vytahne klice z Equipment::STATUSES, tedy
            // ['active', 'service', 'retired'] - seznam je jen na jednom miste.
            'status' => ['required', Rule::in(array_keys(Equipment::STATUSES))],

            // ZADANI: datum nakupu nesmi byt v nesmyslnem formatu.
            // Pravidlo 'date' prijme jen to, co se da precist jako datum.
            'purchased_at' => ['required', 'date'],
        ];
    }
}
