<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
 * ROUTY = tabulka "jaka adresa -> ktera metoda kontroleru".
 *
 * Kazda routa ma tri casti:
 *   Route::get(...)          HTTP metoda (get = otevreni stranky, post = odeslani
 *                            formulare, put = uprava, delete = smazani)
 *   '/books/{book}'          adresa; {book} je promenna cast - Laravel podle ni
 *                            sam najde knihu a podstrci ji do kontroleru
 *   ->name('books.show')     pojmenovani, abychom v sablonach psali
 *                            route('books.show', $book) misto rucni adresy.
 *                            Kdyz se adresa zmeni, sablony menit nemusime.
 */

Route::redirect('/', '/books');

// Knihy - plny CRUD.
// POZOR na poradi: /books/create musi byt DRIV nez /books/{book}, jinak by
// Laravel slovo "create" povazoval za id knihy a hledal knihu s id "create".
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

// Ctenari - jen vypis, pridani a smazani (CRUD zadani vyzadovalo u knih)
Route::get('/readers', [ReaderController::class, 'index'])->name('readers.index');
Route::post('/readers', [ReaderController::class, 'store'])->name('readers.store');
Route::delete('/readers/{reader}', [ReaderController::class, 'destroy'])->name('readers.destroy');

// Vypujcky - nemaji formular na samostatne strance, zakladaji se
// z detailu knihy, proto tu neni create ani edit.
Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
// patch = zmena jedne veci u existujiciho zaznamu (zapis datumu vraceni)
Route::patch('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');

/*
 * EVIDENCE TECHNIKY A SERVISNICH ZASAHU
 */

// Export servisni historie do CSV.
// Musi byt DRIV nez Route::resource, jinak by se /equipment/{equipment}
// chytilo prvni a slovo "export" by Laravel bral jako id zarizeni.
Route::get('/equipment/{equipment}/export', [ServiceRecordController::class, 'export'])
    ->name('equipment.export');

// Route::resource vyrobi jednim radkem vsech 7 standardnich rout
// (index, create, store, show, edit, update, destroy) vcetne jejich nazvu
// equipment.index, equipment.create, ... Vypis rout: php artisan route:list
Route::resource('equipment', EquipmentController::class);

// Servisni zaznamy - pridavaji se z detailu zarizeni, proto je zarizeni
// soucasti adresy a ne formulare
Route::post('/equipment/{equipment}/service-records', [ServiceRecordController::class, 'store'])
    ->name('service-records.store');
Route::delete('/service-records/{serviceRecord}', [ServiceRecordController::class, 'destroy'])
    ->name('service-records.destroy');

// Seznam úkolů
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::patch('/tasks/{task}', [TaskController::class, 'toggle'])->name('tasks.toggle');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

// Evidence studentů
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
