<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL UCASTNIKA
 *
 * Odpovida tabulce "participants" (Participant -> participants).
 */
class Participant extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce povolene pro hromadne plneni (viz komentar v modelu Book)
     */
    protected $fillable = ['name', 'email'];

    /**
     * VAZBA 1:N - jeden ucastnik muze mit vice prihlasek (na ruzne kurzy,
     * nebo i vicekrat na stejny kurz v case - napr. po zruseni a znovu
     * prihlaseni).
     *
     * Pouziti: $participant->enrollments
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
