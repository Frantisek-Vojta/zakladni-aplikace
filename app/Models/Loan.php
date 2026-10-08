<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL VYPUJCKY
 *
 * Vypujcka je spojovaci tabulka mezi knihou a ctenarem - drzi informaci
 * "kdo si kdy co pujcil a do kdy to ma vratit".
 *
 * Jak se pozna, v jakem je vypujcka stavu? Podle sloupce returned_at:
 *   returned_at = NULL   -> kniha je jeste pujcena
 *   returned_at = datum  -> kniha uz byla vracena
 * Zadny sloupec "stav" tedy nepotrebujeme, da se dopocitat.
 */
class Loan extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni (viz komentar v modelu Book)
     */
    protected $fillable = ['book_id', 'reader_id', 'borrowed_at', 'due_at', 'returned_at'];

    /**
     * PRETYPOVANI SLOUPCU
     *
     * Z databaze prijdou data jako text ("2026-10-08"). Diky 'date' z nich
     * Laravel udela objekty Carbon, takze pak muzeme volat napriklad
     * $loan->due_at->format('d.m.Y') nebo $loan->due_at->isPast().
     * Bez tohoto by to byl jen retezec a zadne metody by nemel.
     */
    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date',
            'due_at' => 'date',
            'returned_at' => 'date',
        ];
    }

    /**
     * VAZBA N:1 - vypujcka patri prave jedne knize.
     *
     * belongsTo = "cizi klic je na MOJI strane" (v tabulce loans je book_id).
     * Je to opacna strana vazby Book::loans().
     *
     * Pouziti: $loan->book->title
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * VAZBA N:1 - vypujcka patri prave jednomu ctenari (sloupec reader_id).
     *
     * Pouziti: $loan->reader->name
     */
    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    /**
     * Je kniha jeste nevracena?
     * Pouziva se napr. k rozhodnuti, jestli zobrazit tlacitko "Vratit".
     */
    public function isActive(): bool
    {
        return $this->returned_at === null;
    }

    /**
     * Je vypujcka po terminu?
     *
     * Musi platit oboji: jeste neni vracena A termin uz uplynul.
     * Pozor na druhou podminku samotnou - vracena kniha uz po terminu neni,
     * i kdyby ji ctenar vratil pozde.
     */
    public function isOverdue(): bool
    {
        return $this->isActive() && $this->due_at->isPast();
    }

    /**
     * SCOPE = pojmenovany filtr dotazu, ktery si muzeme zavolat kdekoliv.
     *
     * Metoda se musi jmenovat scopeNazev(), ale vola se bez predpony "scope":
     *   Loan::overdue()->get()
     *
     * Rozdil proti metode isOverdue() vyse: isOverdue() se pta u JEDNE uz
     * nactene vypujcky, scope vybira vypujcky primo v databazi (SQL WHERE),
     * takze netahame z DB zbytecne radky.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('returned_at')->whereDate('due_at', '<', now());
    }
}
