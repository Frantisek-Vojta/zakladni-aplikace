<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    /**
     * Tabulka nema created_at / updated_at
     */
    public $timestamps = false;

    /**
     * Sloupce
     */
    protected $fillable = ['book_id', 'reader_id', 'borrowed_at', 'due_at', 'returned_at'];

    /**
     * Data chceme jako objekty Carbon, ne jako text
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
     * Vypujcka patri prave jedne knize
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Vypujcka patri prave jednomu ctenari
     */
    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    /**
     * Je kniha jeste nevracena?
     */
    public function isActive(): bool
    {
        return $this->returned_at === null;
    }

    /**
     * Je vypujcka po terminu? (jeste nevracena a termin uz uplynul)
     */
    public function isOverdue(): bool
    {
        return $this->isActive() && $this->due_at->isPast();
    }

    /**
     * Filtr: jen vypujcky po terminu
     * Pouziti: Loan::overdue()->get()
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('returned_at')->whereDate('due_at', '<', now());
    }
}
