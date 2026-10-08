@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <h1>{{ $book->title }}</h1>

    <p>
        Autor: {{ $book->author }}<br>
        ISBN: <span class="muted">{{ $book->isbn }}</span><br>
        Dostupných výtisků: <strong>{{ $book->available_copies }}</strong>
    </p>

    <p><a href="{{ route('books.edit', $book) }}">Upravit knihu</a></p>

    <h2>Nová výpůjčka</h2>

    {{--
        Formular na pujceni se zobrazi jen kdyz to ma smysl:
        nejdriv musi existovat ctenar a kniha musi mit dostupny vytisk.
        Je to jen pohodli pro uzivatele - skutecnou kontrolu dela
        LoanController::store(), protoze sablone nelze verit.
    --}}
    @if ($readers->isEmpty())
        <p class="muted">Nejdřív přidej čtenáře na stránce <a href="{{ route('readers.index') }}">Čtenáři</a>.</p>
    @elseif (! $book->isAvailable())
        <p class="muted">Kniha není k dispozici – všechny výtisky jsou půjčené.</p>
    @else
        <form class="stack" method="POST" action="{{ route('loans.store') }}">
            @csrf
            {{--
                Knihu posilame skrytym polickem - uz vime, o kterou jde,
                takze ji uzivatel nevybira. Server si ji presto overuje
                (pravidlo exists:books,id), protoze skryte policko se da
                v prohlizeci prepsat. Nikdy neverit datum z formulare!
            --}}
            <input type="hidden" name="book_id" value="{{ $book->id }}">

            <label>
                Čtenář
                <select name="reader_id">
                    @foreach ($readers as $reader)
                        <option value="{{ $reader->id }}" @selected(old('reader_id') == $reader->id)>{{ $reader->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Datum výpůjčky
                <input type="date" name="borrowed_at" value="{{ old('borrowed_at', now()->toDateString()) }}">
            </label>

            <label>
                Vrátit do
                <input type="date" name="due_at" value="{{ old('due_at', now()->addMonth()->toDateString()) }}">
            </label>

            <button type="submit">Půjčit</button>
        </form>

        @error('book_id') <p class="error">{{ $message }}</p> @enderror
        @error('reader_id') <p class="error">{{ $message }}</p> @enderror
        @error('borrowed_at') <p class="error">{{ $message }}</p> @enderror
        @error('due_at') <p class="error">{{ $message }}</p> @enderror
    @endif

    <h2>Výpůjčky této knihy</h2>

    <table>
        <tr>
            <th>Čtenář</th>
            <th>Půjčeno</th>
            <th>Vrátit do</th>
            <th>Stav</th>
            <th></th>
        </tr>
        @forelse ($loans as $loan)
            <tr>
                <td>{{ $loan->reader->name }}</td>
                <td>{{ $loan->borrowed_at->format('d.m.Y') }}</td>
                <td>{{ $loan->due_at->format('d.m.Y') }}</td>
                <td>
                    {{-- Stav vypujcky se dopocitava, v databazi zadny sloupec "stav" neni --}}
                    @if ($loan->returned_at)
                        <span class="badge returned">Vráceno {{ $loan->returned_at->format('d.m.Y') }}</span>
                    @elseif ($loan->isOverdue())
                        <span class="badge overdue">Po termínu</span>
                    @else
                        <span class="badge">Půjčeno</span>
                    @endif
                </td>
                <td>
                    @if ($loan->isActive())
                        <form method="POST" action="{{ route('loans.return', $loan) }}">
                            @csrf
                            @method('PATCH')
                            <button class="small" type="submit">Vrátit</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="muted">Kniha nebyla zatím půjčena.</td>
            </tr>
        @endforelse
    </table>

    <p><a href="{{ route('books.index') }}">← Zpět na seznam knih</a></p>
@endsection
