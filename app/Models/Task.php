<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Sloupce, které lze hromadně plnit (mass assignment).
     */
    protected $fillable = ['title'];
}
