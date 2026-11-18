<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL PRIHLASKY
 *
 * Prihlaska je spojovaci tabulka mezi kurzem a ucastnikem - drzi informaci
 * "kdo se kdy na jaky kurz prihlasil a v jakem je to stavu".
 */
class Enrollment extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni (viz komentar v modelu Book)
     */
    protected $fillable = ['course_id', 'participant_id', 'enrolled_at', 'status'];

    /**
     * POVOLENE STAVY PRIHLASKY (domaci prace ze zadani) + jejich cesky popis.
     *
     * Je to na jednom miste, aby se to neopakovalo: pouziva to validace
     * v EnrollmentController (Rule::in), rozbalovaci seznam ve formulari
     * i barevny odznak ve vypisu. Kdyz se prida novy stav, pridava se jen sem.
     *
     *   enrolled  - ucastnik je aktivne prihlasen (vychozi stav po prihlaseni)
     *   cancelled - prihlaska byla zrusena, uvolni misto v kapacite kurzu
     *   completed - ucastnik kurz absolvoval (kurz uz probehl)
     */
    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_ENROLLED => 'Přihlášen',
        self::STATUS_CANCELLED => 'Zrušeno',
        self::STATUS_COMPLETED => 'Dokončeno',
    ];

    /**
     * Datum prihlaseni chceme jako Carbon, aby slo volat ->format('d.m.Y')
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
        ];
    }

    /**
     * VAZBA N:1 - prihlaska patri prave jednomu kurzu.
     *
     * Pouziti: $enrollment->course->name
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * VAZBA N:1 - prihlaska patri prave jednomu ucastnikovi.
     *
     * Pouziti: $enrollment->participant->name
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Je prihlaska aktivni? (neni zrusena)
     *
     * Pouziva se napr. k rozhodnuti, jestli prihlaska pocita do kapacity
     * kurzu a jestli se u ni maji nabizet tlacitka "Zrusit" / "Dokoncit".
     */
    public function isActive(): bool
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    /**
     * SCOPE = vyber jen aktivni (nezrusene) prihlasky.
     * Pouziti: Enrollment::active()->count()
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Cesky nazev stavu - z 'enrolled' udela 'Přihlášen'.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nazev CSS tridy pro barevne odliseni stavu ve vypisu.
     * Barvy jsou v layoutu (resources/views/layouts/app.blade.php).
     */
    public function statusClass(): string
    {
        return 'status-'.$this->status;
    }
}
