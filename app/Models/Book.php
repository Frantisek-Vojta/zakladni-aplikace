<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce
     */
    protected $fillable = ['title', 'author', 'isbn', 'available_copies'];

    /**
     * Jedna kniha muze mit vice vypujcek
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Je k dispozici alespon jeden vytisk?
     */
    public function isAvailable(): bool
    {
        return $this->available_copies > 0;
    }
}
