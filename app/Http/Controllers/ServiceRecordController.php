<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\ServiceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * KONTROLER SERVISNICH ZAZNAMU
 *
 * Zaznamy nemaji vlastni stranku se seznamem - pridavaji se primo
 * z detailu zarizeni, proto tu nejsou metody index/create/edit.
 */
class ServiceRecordController extends Controller
{
    /**
     * Prida servisni zaznam k zarizeni.
     *
     * Zarizeni prijde z adresy (/equipment/3/service-records), takze ho
     * uzivatel neposila ve formulari a nemuze ho podvrhnout.
     */
    public function store(Request $request, Equipment $equipment): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],

            // ZADANI: datum servisu nesmi byt v nesmyslnem formatu
            'serviced_at' => ['required', 'date'],

            // ZADANI: cena je nezaporne desetinne cislo.
            //   numeric = cislo (prijme i desetinne, na rozdil od integer)
            //   min:0   = nesmi byt zaporna
            'cost' => ['required', 'numeric', 'min:0'],
        ]);

        // Zaznam zakladame pres vazbu, takze se equipment_id vyplni samo
        // podle zarizeni z adresy.
        $equipment->serviceRecords()->create($data);

        return redirect()->route('equipment.show', $equipment);
    }

    /**
     * Smaze servisni zaznam
     */
    public function destroy(ServiceRecord $serviceRecord): RedirectResponse
    {
        // zarizeni si zapamatujeme pred smazanim, abychom vedeli kam vratit
        $equipment = $serviceRecord->equipment;

        $serviceRecord->delete();

        return redirect()->route('equipment.show', $equipment);
    }

    /**
     * EXPORT SERVISNI HISTORIE DO CSV (volitelne rozsireni ze zadani)
     *
     * StreamedResponse = odpoved se posila po castech, jak se generuje.
     * Nemusi se tedy cely soubor drzet v pameti, coz by u tisicu radku vadilo.
     */
    public function export(Equipment $equipment): StreamedResponse
    {
        $records = $equipment->serviceRecords()->orderBy('serviced_at')->get();

        // nazev souboru podle evidencniho cisla, napr. servis-NB-001.csv
        $filename = 'servis-'.$equipment->inventory_number.'.csv';

        return response()->streamDownload(function () use ($equipment, $records) {
            // php://output = rovnou do odpovedi pro prohlizec
            $handle = fopen('php://output', 'w');

            // BOM = znacka na zacatku souboru, aby Excel poznal kodovani UTF-8
            // a nerozsypal cestinu
            fwrite($handle, "\xEF\xBB\xBF");

            // fputcsv sam vyresi uvozovky a strednik, kdyz je text obsahuje
            fputcsv($handle, ['Zařízení', 'Evidenční číslo', 'Popis', 'Datum servisu', 'Cena'], ';');

            foreach ($records as $record) {
                fputcsv($handle, [
                    $equipment->name,
                    $equipment->inventory_number,
                    $record->description,
                    $record->serviced_at->format('d.m.Y'),
                    $record->cost,
                ], ';');
            }

            // posledni radek se souctem
            fputcsv($handle, ['', '', 'CELKEM', '', $equipment->totalServiceCost()], ';');

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
