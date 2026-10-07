<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reader extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce
     */
    protected $fillable = ['name', 'email'];

    /**
     * Jeden ctenar muze mit vice vypujcek
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
