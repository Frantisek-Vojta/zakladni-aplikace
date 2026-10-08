<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reader;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Nahraje ukazkova data: knihy, ctenare a par vypujcek.
     * Spusteni: php artisan db:seed --class=LibrarySeeder
     */
    public function run(): void
    {
        // --- knihy ---
        // available_copies = kolik vytisku je prave k dispozici
        $babicka = Book::create([
            'title' => 'Babička',
            'author' => 'Božena Němcová',
            'isbn' => '9788074990014',
            'available_copies' => 2,
        ]);

        $hobit = Book::create([
            'title' => 'Hobit',
            'author' => 'J. R. R. Tolkien',
            'isbn' => '9788025703021',
            'available_copies' => 3,
        ]);

        $svejk = Book::create([
            'title' => 'Osudy dobrého vojáka Švejka',
            'author' => 'Jaroslav Hašek',
            'isbn' => '9788024212345',
            'available_copies' => 1,
        ]);

        $rur = Book::create([
            'title' => 'R.U.R.',
            'author' => 'Karel Čapek',
            'isbn' => '9788073900123',
            'available_copies' => 2,
        ]);

        Book::create([
            'title' => 'Malý princ',
            'author' => 'Antoine de Saint-Exupéry',
            'isbn' => '9788025712345',
            'available_copies' => 1,
        ]);

        // --- ctenari ---
        $jan = Reader::create(['name' => 'Jan Novák', 'email' => 'jan.novak@example.com']);
        $eva = Reader::create(['name' => 'Eva Malá', 'email' => 'eva.mala@example.com']);
        $petr = Reader::create(['name' => 'Petr Svoboda', 'email' => 'petr.svoboda@example.com']);

        // --- vypujcky ---
        // 1) bezna pujcena kniha, termin jeste neuplynul
        $this->lend($hobit, $jan, borrowedDaysAgo: 5, dueInDays: 25);

        // 2) vypujcka PO TERMINU - ukaze se cervene v prehledu vypujcek
        $this->lend($babicka, $eva, borrowedDaysAgo: 40, dueInDays: -10);

        // 3) posledni vytisk je pujceny -> kniha uz neni k dispozici
        $this->lend($svejk, $petr, borrowedDaysAgo: 2, dueInDays: 28);

        // 4) uz vracena vypujcka (vytisk se nestrhava, kniha je zpatky ve skladu)
        Loan::create([
            'book_id' => $rur->id,
            'reader_id' => $petr->id,
            'borrowed_at' => now()->subDays(60),
            'due_at' => now()->subDays(30),
            'returned_at' => now()->subDays(35),
        ]);
    }

    /**
     * Pujci knihu ctenari - stejne jako v aplikaci ubere jeden dostupny vytisk,
     * aby ukazkova data odpovidala skutecnosti.
     */
    private function lend(Book $book, Reader $reader, int $borrowedDaysAgo, int $dueInDays): void
    {
        Loan::create([
            'book_id' => $book->id,
            'reader_id' => $reader->id,
            'borrowed_at' => now()->subDays($borrowedDaysAgo),
            'due_at' => now()->addDays($dueInDays),
            'returned_at' => null,
        ]);

        $book->decrement('available_copies');
    }
}
