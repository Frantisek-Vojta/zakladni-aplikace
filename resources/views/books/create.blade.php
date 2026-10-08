@extends('layouts.app')

@section('title', 'Nová kniha')

@section('content')
    <h1>Nová kniha</h1>

    <form class="stack" method="POST" action="{{ route('books.store') }}">
        {{--
            @csrf vlozi do formulare skryty bezpecnostni token.
            Chrani pred utokem CSRF: bez nej by cizi web mohl podstrcit
            formular, ktery by za prihlaseneho uzivatele neco ulozil.
            Laravel token kontroluje u kazdeho POST/PUT/PATCH/DELETE -
            kdyz chybi, vrati chybu 419.
        --}}
        @csrf

        <label>
            Název
            {{--
                old('title') vrati to, co uzivatel naposledy napsal.
                Vyuzije se kdyz validace neprojde - formular se vrati
                vyplneny a uzivatel nemusi psat vsechno znovu.
            --}}
            <input type="text" name="title" value="{{ old('title') }}" autofocus>
        </label>
        {{-- @error vypise chybu validace pro dane policko (sem ji posila validate()) --}}
        @error('title') <p class="error">{{ $message }}</p> @enderror

        <label>
            Autor
            <input type="text" name="author" value="{{ old('author') }}">
        </label>
        @error('author') <p class="error">{{ $message }}</p> @enderror

        <label>
            ISBN (13 znaků)
            <input type="text" name="isbn" value="{{ old('isbn') }}">
        </label>
        @error('isbn') <p class="error">{{ $message }}</p> @enderror

        <label>
            Počet dostupných výtisků
            <input type="number" name="available_copies" min="0" value="{{ old('available_copies', 1) }}">
        </label>
        @error('available_copies') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit</button>
    </form>

    <p><a href="{{ route('books.index') }}">← Zpět na seznam</a></p>
@endsection
