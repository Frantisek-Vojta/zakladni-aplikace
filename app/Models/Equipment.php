<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL ZARIZENI
 *
 * Pozor na nazev tabulky: Laravel ho odvozuje z nazvu tridy v mnoznem cisle
 * (Book -> books). Slovo "equipment" ale mnozne cislo nema, proto mu tabulku
 * urcime rucne pres $table.
 */
class Equipment extends Model
{
    use HasFactory;

    /**
     * Nazev tabulky musime urcit rucne (viz komentar u tridy)
     */
    protected $table = 'equipment';

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni (ochrana pred mass assignment)
     */
    protected $fillable = ['name', 'inventory_number', 'status', 'purchased_at'];

    /**
     * POVOLENE STAVY + jejich cesky popis.
     *
     * Je to na jednom miste, aby se to neopakovalo: pouziva to validace
     * v kontroleru (Rule::in), rozbalovaci seznam ve formulari i filtr.
     * Kdyz se prida novy stav, pridava se jen sem.
     */
    public const STATUSES = [
        'active' => 'V provozu',
        'service' => 'V servisu',
        'retired' => 'Vyřazeno',
    ];

    /**
     * Datum chceme jako objekt Carbon, aby slo volat ->format('d.m.Y')
     */
    protected function casts(): array
    {
        return [
            'purchased_at' => 'date',
        ];
    }

    /**
     * VAZBA 1:N - jedno zarizeni ma vice servisnich zaznamu.
     * Laravel hleda v tabulce service_records sloupec equipment_id.
     *
     * Pouziti: $equipment->serviceRecords
     */
    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    /**
     * SOUCET SERVISNICH NAKLADU tohoto zarizeni.
     *
     * sum('cost') secte sloupec cost primo v databazi (SQL SUM), takze
     * se netahaji vsechny zaznamy do PHP jen proto, aby se secetly.
     * Kdyz zarizeni zadny servis nemelo, vrati 0.
     */
    public function totalServiceCost(): float
    {
        return (float) $this->serviceRecords()->sum('cost');
    }

    /**
     * Cesky nazev stavu - z 'active' udela 'V provozu'.
     * Druhy parametr je zaloha pro pripad neznameho stavu.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nazev CSS tridy pro barevne odliseni stavu.
     * Samotne barvy jsou v sablone layoutu (resources/views/layouts/app.blade.php).
     */
    public function statusClass(): string
    {
        return 'status-'.$this->status;
    }
}
