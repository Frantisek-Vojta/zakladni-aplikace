<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL KNIHY
 *
 * Model = jeden radek v tabulce "books". Laravel si nazev tabulky odvodi
 * sam z nazvu tridy: Book -> books (mnozne cislo, mala pocatecni pismena).
 */
class Book extends Model
{
    use HasFactory;

    /**
     * Tabulka nema sloupce created_at / updated_at.
     * Kdyby to tu nebylo, Laravel by je chtel zapisovat a spadlo by to.
     */
    public $timestamps = false;

    /**
     * Sloupce, ktere se smi plnit hromadne pres create() nebo update().
     *
     * Proc to tu je: chrani to pred utokem "mass assignment". Kdyby tu byl
     * treba i sloupec "id", mohl by nekdo poslat ve formulari id=5 a prepsat
     * si cizi zaznam. Co neni v tomto seznamu, Laravel ignoruje.
     */
    protected $fillable = ['title', 'author', 'isbn', 'available_copies'];

    /**
     * VAZBA 1:N - jedna kniha muze mit vice vypujcek.
     *
     * hasMany = "na druhe strane je jich mnoho". Laravel hleda v tabulce
     * "loans" sloupec book_id (nazev modelu + _id).
     *
     * Pouziti: $book->loans  (vrati kolekci vypujcek teto knihy)
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Je k dispozici alespon jeden vytisk?
     *
     * Je to v modelu a ne v kontroleru schvalne: pravidlo "kniha je dostupna"
     * patri ke knize. Diky tomu ho muzeme pouzit i v kontroleru i v sablone
     * a mame ho napsane jen jednou.
     */
    public function isAvailable(): bool
    {
        return $this->available_copies > 0;
    }
}
