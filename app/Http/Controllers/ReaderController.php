<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * KONTROLER CTENARU
 *
 * Zadani vyzadovalo plny CRUD jen u knih, takze tady je zamerne jen
 * to potrebne: vypsat, pridat, smazat.
 */
class ReaderController extends Controller
{
    /**
     * Seznam ctenaru + u kazdeho pocet nevracenych knih
     */
    public function index(): View
    {
        // withCount() prida ke kazdemu ctenari spocitany sloupec, aniz by
        // tahal z DB cele vypujcky. Vysledek je v $reader->active_loans_count
        // ("as active_loans_count" urcuje nazev toho sloupce).
        // Uvnitr filtrujeme jen nevracene vypujcky (returned_at je NULL).
        $readers = Reader::withCount(['loans as active_loans_count' => function ($query) {
            $query->whereNull('returned_at');
        }])->orderBy('name')->get();

        return view('readers.index', ['readers' => $readers]);
    }

    /**
     * Ulozi noveho ctenare
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // email = musi to byt platny tvar e-mailu
            // unique = jeden e-mail smi byt v tabulce jen jednou
            'email' => ['required', 'email', 'max:255', Rule::unique('readers', 'email')],
        ]);

        Reader::create($data);

        return redirect()->route('readers.index');
    }

    /**
     * Smaze ctenare.
     * Jeho vypujcky se smazou samy diky cascadeOnDelete() v migraci loans.
     */
    public function destroy(Reader $reader): RedirectResponse
    {
        $reader->delete();

        return redirect()->route('readers.index');
    }
}
