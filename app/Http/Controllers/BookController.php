<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * KONTROLER KNIH - kompletni CRUD
 *
 * CRUD = Create (create + store), Read (index + show),
 *        Update (edit + update), Delete (destroy).
 *
 * Proc dve metody na jednu vec? Jedna zobrazi formular (GET),
 * druha zpracuje odeslana data (POST/PUT). Formular a jeho ulozeni
 * jsou dva samostatne pozadavky z prohlizece.
 */
class BookController extends Controller
{
    /**
     * READ - seznam knih + filtr podle nazvu nebo autora
     */
    public function index(Request $request): View
    {
        // z adresy /books?search=Tolkien vytahneme hledany text
        $search = $request->query('search');

        $books = Book::query()
            // when() = "pridej tuto cast dotazu jen kdyz je podminka splnena".
            // Kdyz uzivatel nic nehleda ($search je null), filtr se vubec
            // nepouzije a vypisou se vsechny knihy.
            ->when($search, function ($query) use ($search) {
                // where ... orWhere = staci, aby text sedel v nazvu NEBO u autora
                // like s % okolo = "obsahuje", ne "presne se rovna"
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->get();

        return view('books.index', [
            'books' => $books,
            // posilame i hledany text, aby zustal napsany v policku
            'search' => $search,
        ]);
    }

    /**
     * CREATE (1/2) - jen zobrazi prazdny formular
     */
    public function create(): View
    {
        return view('books.create');
    }

    /**
     * CREATE (2/2) - ulozi novou knihu
     */
    public function store(Request $request): RedirectResponse
    {
        // validate() pri chybe sam prerusi beh metody, vrati uzivatele
        // zpet na formular a do sablony posle chybove zpravy.
        // Dal se tedy dostaneme jen kdyz jsou data v poradku.
        $data = $request->validate($this->rules());

        Book::create($data);

        // po ulozeni presmerujeme (ne return view!), aby uzivatel pri
        // obnoveni stranky neposlal formular podruhe
        return redirect()->route('books.index');
    }

    /**
     * READ - detail knihy se seznamem jejich vypujcek
     *
     * Book $book v parametru = "route model binding": v adrese je /books/3,
     * Laravel sam najde knihu s id 3 a podstrci ji sem. Kdyz neexistuje,
     * vrati automaticky chybu 404.
     */
    public function show(Book $book): View
    {
        $loans = $book->loans()
            // with('reader') resi problem N+1 dotazu: bez toho by se pro
            // KAZDOU vypujcku delal zvlastni dotaz na jejiho ctenare.
            // Takhle se nactou vsichni ctenari jednim dotazem navic.
            ->with('reader')
            ->orderByDesc('borrowed_at')
            ->get();

        return view('books.show', [
            'book' => $book,
            'loans' => $loans,
            // ctenare posilame do rozbalovaciho seznamu ve formulari vypujcky
            'readers' => Reader::orderBy('name')->get(),
        ]);
    }

    /**
     * UPDATE (1/2) - formular predplneny soucasnymi hodnotami
     */
    public function edit(Book $book): View
    {
        return view('books.edit', ['book' => $book]);
    }

    /**
     * UPDATE (2/2) - ulozi zmeny
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        // knihu predame do rules(), aby si pri kontrole jedinecnosti ISBN
        // "nestezovala sama na sebe" - viz komentar v rules()
        $data = $request->validate($this->rules($book));

        $book->update($data);

        return redirect()->route('books.show', $book);
    }

    /**
     * DELETE - smaze knihu
     *
     * Vypujcky teto knihy se smazou samy diky cascadeOnDelete()
     * nastavenemu v migraci tabulky loans.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('books.index');
    }

    /**
     * VALIDACNI PRAVIDLA PRO KNIHU
     *
     * Jsou v jedne metode, aby se neopakovala ve store() i update()
     * (kdyz se pravidlo zmeni, menim ho na jednom miste).
     *
     * $book je vyplnene jen pri uprave, pri zakladani nove knihy je null.
     */
    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],

            // ZADANI: ISBN je jedinecne a ma 13 znaku
            //   size:13 = presne 13 znaku (ne min, ne max)
            //   unique  = v tabulce books nesmi byt stejne ISBN dvakrat
            //   ->ignore($book) = pri UPRAVE vynech z kontroly knihu samotnou.
            //     Bez toho by sla kniha ulozit jen po zmene ISBN - jeji vlastni
            //     ISBN uz je totiz v tabulce a unique by ho oznacil za duplikat.
            'isbn' => ['required', 'string', 'size:13', Rule::unique('books', 'isbn')->ignore($book)],

            // ZADANI: available_copies je cele cislo nejmene 0
            'available_copies' => ['required', 'integer', 'min:0'],
        ];
    }
}
