<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * seznam vsech vypujcek + vypujcky po terminu
     */
    public function index(): View
    {
        return view('loans.index', [
            // with() nacte knihu a ctenare jednim dotazem
            'loans' => Loan::with(['book', 'reader'])->orderByDesc('id')->get(),
            'overdueLoans' => Loan::overdue()->with(['book', 'reader'])->orderBy('due_at')->get(),
        ]);
    }

    /**
     * zaloz novou vypujcku
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'reader_id' => ['required', 'exists:readers,id'],
            'borrowed_at' => ['required', 'date'],
            // termin vraceni musi byt pozdeji nez datum vypujceni
            'due_at' => ['required', 'date', 'after:borrowed_at'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        // 1) vypujcku lze zalozit jen kdyz je dostupny vytisk
        if (! $book->isAvailable()) {
            return back()
                ->withErrors(['book_id' => 'Tato kniha nemá žádný dostupný výtisk.'])
                ->withInput();
        }

        // 2) stejnou knihu nelze jednomu ctenari pujcit podruhe, dokud ji nevratil
        $alreadyBorrowed = Loan::where('book_id', $data['book_id'])
            ->where('reader_id', $data['reader_id'])
            ->whereNull('returned_at')
            ->exists();

        if ($alreadyBorrowed) {
            return back()
                ->withErrors(['reader_id' => 'Tento čtenář má tuto knihu ještě nevrácenou.'])
                ->withInput();
        }

        Loan::create($data);

        // vytisk je pujceny, takze ubyde ze skladu
        $book->decrement('available_copies');

        return redirect()->route('books.show', $book);
    }

    /**
     * vraceni knihy
     */
    public function returnBook(Loan $loan): RedirectResponse
    {
        // vracet se da jen nevracena vypujcka (aby se vytisk nepripocetl dvakrat)
        if ($loan->isActive()) {
            $loan->update(['returned_at' => now()]);

            $loan->book->increment('available_copies');
        }

        return back();
    }
}
