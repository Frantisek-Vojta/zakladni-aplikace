<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * seznam knih + filtr podle nazvu nebo autora
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $books = Book::query()
            // filtrujeme jen kdyz uzivatel neco napsal do vyhledavaciho policka
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->get();

        return view('books.index', [
            'books' => $books,
            'search' => $search,
        ]);
    }

    /**
     * formular pro novou knihu
     */
    public function create(): View
    {
        return view('books.create');
    }

    /**
     * ulozi novou knihu
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        Book::create($data);

        return redirect()->route('books.index');
    }

    /**
     * detail knihy se seznamem vypujcek
     */
    public function show(Book $book): View
    {
        // nacteme vypujcky i se ctenari (aby se nedelal dotaz do DB pro kazdy radek)
        $loans = $book->loans()->with('reader')->orderByDesc('borrowed_at')->get();

        return view('books.show', [
            'book' => $book,
            'loans' => $loans,
            'readers' => Reader::orderBy('name')->get(),
        ]);
    }

    /**
     * formular pro upravu knihy
     */
    public function edit(Book $book): View
    {
        return view('books.edit', ['book' => $book]);
    }

    /**
     * ulozi upravenou knihu
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        // pri uprave musime z kontroly jedinecnosti vynechat knihu samotnou
        $data = $request->validate($this->rules($book));

        $book->update($data);

        return redirect()->route('books.show', $book);
    }

    /**
     * smaze knihu (i s jejimi vypujckami)
     */
    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('books.index');
    }

    /**
     * Validacni pravidla pro knihu.
     * $book je vyplnene jen pri uprave - pak se jeho ISBN pri kontrole jedinecnosti ignoruje.
     */
    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            // ISBN je jedinecne a ma presne 13 znaku
            'isbn' => ['required', 'string', 'size:13', Rule::unique('books', 'isbn')->ignore($book)],
            // pocet vytisku je cele cislo, nejmene 0
            'available_copies' => ['required', 'integer', 'min:0'],
        ];
    }
}
