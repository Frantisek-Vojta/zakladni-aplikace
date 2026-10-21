<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL SERVISNIHO ZAZNAMU
 *
 * Jeden servisni zasah na jednom zarizeni.
 */
class ServiceRecord extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni
     */
    protected $fillable = ['equipment_id', 'description', 'serviced_at', 'cost'];

    /**
     * Pretypovani sloupcu:
     *   date       -> objekt Carbon, aby slo volat ->format('d.m.Y')
     *   decimal:2  -> cislo vzdy na dve desetinna mista (penize)
     */
    protected function casts(): array
    {
        return [
            'serviced_at' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    /**
     * VAZBA N:1 - zaznam patri prave jednomu zarizeni.
     * belongsTo = cizi klic (equipment_id) je v teto tabulce.
     *
     * Pouziti: $record->equipment->name
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
