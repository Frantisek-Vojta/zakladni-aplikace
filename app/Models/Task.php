<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce
     */
    protected $fillable = ['title', 'is_done'];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }
}
