@extends('layouts.app')

@section('title', 'Knihy')

@section('content')
    <h1>Knihy</h1>

    {{-- Filtr podle nazvu nebo autora (GET, aby se dal odkaz nasdilet) --}}
    <form class="add" method="GET" action="{{ route('books.index') }}">
        <input type="text" name="search" placeholder="Hledat podle názvu nebo autora" value="{{ $search }}">
        <button type="submit">Hledat</button>
        @if ($search)
            <a href="{{ route('books.index') }}"><button class="small" type="button">Zrušit filtr</button></a>
        @endif
    </form>

    <p><a href="{{ route('books.create') }}">+ Přidat knihu</a></p>

    <table>
        <tr>
            <th>Název</th>
            <th>Autor</th>
            <th>ISBN</th>
            <th>Dostupné</th>
            <th></th>
        </tr>
        @forelse ($books as $book)
            <tr>
                <td><a href="{{ route('books.show', $book) }}">{{ $book->title }}</a></td>
                <td>{{ $book->author }}</td>
                <td class="muted">{{ $book->isbn }}</td>
                <td>{{ $book->available_copies }}</td>
                <td>
                    <a href="{{ route('books.edit', $book) }}">Upravit</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="muted">Žádné knihy nenalezeny.</td>
            </tr>
        @endforelse
    </table>
@endsection
