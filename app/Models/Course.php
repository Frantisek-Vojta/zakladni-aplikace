<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL KURZU
 *
 * Model = jeden radek v tabulce "courses". Laravel si nazev tabulky odvodi
 * sam z nazvu tridy: Course -> courses.
 */
class Course extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni (ochrana pred mass assignment -
     * viz podrobny komentar v modelu Book)
     */
    protected $fillable = ['name', 'code', 'capacity', 'starts_at'];

    /**
     * Datum zacatku chceme jako objekt Carbon, aby slo volat napr.
     * $course->starts_at->format('d.m.Y') nebo ->isPast()
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
        ];
    }

    /**
     * VAZBA 1:N - jeden kurz muze mit vice prihlasek.
     *
     * Pouziti: $course->enrollments
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * POCET AKTIVNICH PRIHLASEK.
     *
     * "Aktivni" = kazda prihlaska krome zrusene (status 'cancelled').
     * Zruseni uvolni misto, dokoncena prihlaska (status 'completed') misto
     * stale "zabira" - ucastnik kurz absolvoval, nejde o neplatny zaznam.
     *
     * Pocitame primo v databazi (COUNT), ne tim, ze bychom tahali vsechny
     * prihlasky do PHP a pocitali je tady.
     */
    public function activeEnrollmentsCount(): int
    {
        return $this->enrollments()->where('status', '!=', Enrollment::STATUS_CANCELLED)->count();
    }

    /**
     * POCET VOLNYCH MIST = kapacita minus aktivni prihlasky.
     *
     * max(0, ...) je pojistka: kapacitu lze snizit i po naplneni kurzu
     * (uprava kurzu), takze bez teto pojistky by tu mohlo vyjit zaporne
     * cislo, coz by ve sablone vypadalo divne ("-2 volna mista").
     */
    public function freeSpots(): int
    {
        return max(0, $this->capacity - $this->activeEnrollmentsCount());
    }

    /**
     * Je kurz uz plny? (zadne volne misto)
     */
    public function isFull(): bool
    {
        return $this->freeSpots() <= 0;
    }

    /**
     * SCOPE = filtr kurzu, ktere zacinaji v dany den nebo pozdeji.
     * Pouziti: Course::startingFrom($datum)->get()
     */
    public function scopeStartingFrom(Builder $query, string $date): void
    {
        $query->whereDate('starts_at', '>=', $date);
    }
}
