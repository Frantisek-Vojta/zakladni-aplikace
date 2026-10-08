@extends('layouts.app')

@section('title', 'Upravit knihu')

@section('content')
    <h1>Upravit knihu</h1>

    <form class="stack" method="POST" action="{{ route('books.update', $book) }}">
        @csrf
        {{--
            @method('PUT') - HTML formular umi poslat jen GET a POST.
            Tahle direktiva prida skryte policko _method=PUT a Laravel se
            pak k pozadavku chova, jako by prisel metodou PUT. Diky tomu
            muzeme mit routu Route::put(...) podle pravidel REST.
        --}}
        @method('PUT')

        <label>
            Název
            {{--
                old('title', $book->title) = dva parametry:
                  1. co uzivatel napsal (kdyz se formular vratil s chybou)
                  2. soucasna hodnota z databaze (kdyz formular otevira poprve)
            --}}
            <input type="text" name="title" value="{{ old('title', $book->title) }}" autofocus>
        </label>
        @error('title') <p class="error">{{ $message }}</p> @enderror

        <label>
            Autor
            <input type="text" name="author" value="{{ old('author', $book->author) }}">
        </label>
        @error('author') <p class="error">{{ $message }}</p> @enderror

        <label>
            ISBN (13 znaků)
            <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}">
        </label>
        @error('isbn') <p class="error">{{ $message }}</p> @enderror

        <label>
            Počet dostupných výtisků
            <input type="number" name="available_copies" min="0" value="{{ old('available_copies', $book->available_copies) }}">
        </label>
        @error('available_copies') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit změny</button>
    </form>

    {{-- Smazani je samostatny formular, protoze pouziva metodu DELETE --}}
    <form method="POST" action="{{ route('books.destroy', $book) }}">
        @csrf
        @method('DELETE')
        <button class="small danger" type="submit">Smazat knihu</button>
    </form>

    <p><a href="{{ route('books.show', $book) }}">← Zpět na detail</a></p>
@endsection
