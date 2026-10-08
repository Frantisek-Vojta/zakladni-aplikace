<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * KONTROLER VYPUJCEK
 *
 * Tady je nejzajimavejsi logika cele aplikace - pujceni a vraceni knihy.
 * Krome formalni validace (datumy, cizi klice) tu resime dve pravidla,
 * ktera se beznym validacnim pravidlem napsat nedaji, protoze se musi
 * zjistovat dotazem do databaze.
 */
class LoanController extends Controller
{
    /**
     * Prehled vypujcek - vsechny + zvlast ty po terminu
     */
    public function index(): View
    {
        return view('loans.index', [
            // with(['book', 'reader']) nacte knihy a ctenare predem jednim
            // dotazem (jinak by se pro kazdy radek tabulky delaly dotazy
            // navic - tzv. problem N+1)
            'loans' => Loan::with(['book', 'reader'])->orderByDesc('id')->get(),

            // overdue() je scope z modelu Loan = nevracene + po terminu
            'overdueLoans' => Loan::overdue()->with(['book', 'reader'])->orderBy('due_at')->get(),
        ]);
    }

    /**
     * ZALOZENI VYPUJCKY
     *
     * Postup: 1) zvalidovat data z formulare
     *         2) overit, ze je dostupny vytisk
     *         3) overit, ze si ctenar tutez knihu uz nepujcil
     *         4) ulozit vypujcku a odecist vytisk
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // exists = v tabulce books/readers takove id opravdu existuje
            // (brani ulozeni vypujcky na neexistujici knihu)
            'book_id' => ['required', 'exists:books,id'],
            'reader_id' => ['required', 'exists:readers,id'],
            'borrowed_at' => ['required', 'date'],

            // ZADANI: due_at je pozdeji nez borrowed_at
            // after:borrowed_at = porovnava s hodnotou jineho policka formulare
            'due_at' => ['required', 'date', 'after:borrowed_at'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        // ZADANI (1): vypujcku lze zalozit jen kdyz je dostupny vytisk.
        //
        // Proc to neni validacni pravidlo? Protoze se to nepozna z odeslanych
        // dat, ale az dotazem na stav knihy v databazi. Proto obycejny if
        // a rucni vraceni chyby:
        //   back()         = vrat uzivatele na stranku, odkud formular poslal
        //   withErrors()   = predej chybovou zpravu do sablony (@error)
        //   withInput()    = zachovej vyplnene hodnoty, aby je nemusel psat znovu
        if (! $book->isAvailable()) {
            return back()
                ->withErrors(['book_id' => 'Tato kniha nemá žádný dostupný výtisk.'])
                ->withInput();
        }

        // ZADANI (2): stejnou knihu nelze jednomu ctenari pujcit podruhe,
        // dokud ji nevratil.
        //
        // Hledame vypujcku se stejnou knihou I stejnym ctenarem, ktera jeste
        // neni vracena (returned_at je NULL). Kdyz takova existuje, odmitneme.
        // exists() je rychlejsi nez get() - zajima nas jen ano/ne, nepotrebujeme
        // tahat cely zaznam.
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

        // jeden vytisk je ted pujceny, takze ubyde z dostupnych.
        // decrement() udela SQL "available_copies = available_copies - 1"
        // primo v databazi - bezpecnejsi nez si cislo nacist, odecist a ulozit.
        $book->decrement('available_copies');

        return redirect()->route('books.show', $book);
    }

    /**
     * VRACENI KNIHY
     *
     * Vypujcku nemazeme - jen do ni zapiseme datum vraceni, aby zustala
     * v historii. A vytisk vratime zpatky mezi dostupne.
     */
    public function returnBook(Loan $loan): RedirectResponse
    {
        // Podminka je tu schvalne: brani dvojitemu vraceni.
        // Kdyby uzivatel kliknul na "Vratit" dvakrat (nebo obnovil stranku),
        // bez teto kontroly by se vytisk pripocetl dvakrat a knihovna by
        // "vyrobila" vytisk, ktery neexistuje.
        if ($loan->isActive()) {
            $loan->update(['returned_at' => now()]);

            // $loan->book = kniha teto vypujcky (pres vazbu belongsTo)
            $loan->book->increment('available_copies');
        }

        // back() = zustan na strance, odkud uzivatel kliknul. Diky tomu
        // funguje tlacitko "Vratit" i v detailu knihy i v prehledu vypujcek.
        return back();
    }
}
