<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL CTENARE
 *
 * Odpovida tabulce "readers" (Reader -> readers).
 */
class Reader extends Model
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
     * VAZBA 1:N - jeden ctenar muze mit vice vypujcek.
     * Laravel hleda v tabulce "loans" sloupec reader_id.
     *
     * Pouziti: $reader->loans
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
