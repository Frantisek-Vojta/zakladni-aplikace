@extends('layouts.app')

@section('title', 'Výpůjčky')

@section('content')
    <h1>Výpůjčky</h1>

    {{-- Tuhle sekci chtelo zadani: prehled vypujcek po terminu.
         Data pripravuje scope Loan::overdue() (nevracene + termin uplynul). --}}
    <h2>Po termínu ({{ $overdueLoans->count() }})</h2>

    <table>
        <tr>
            <th>Kniha</th>
            <th>Čtenář</th>
            <th>Mělo být vráceno</th>
            <th></th>
        </tr>
        @forelse ($overdueLoans as $loan)
            <tr>
                <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                <td>{{ $loan->reader->name }}</td>
                <td><span class="badge overdue">{{ $loan->due_at->format('d.m.Y') }}</span></td>
                <td>
                    <form method="POST" action="{{ route('loans.return', $loan) }}">
                        @csrf
                        @method('PATCH')
                        <button class="small" type="submit">Vrátit</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">Žádná výpůjčka není po termínu.</td>
            </tr>
        @endforelse
    </table>

    <h2>Všechny výpůjčky ({{ $loans->count() }})</h2>

    <table>
        <tr>
            <th>Kniha</th>
            <th>Čtenář</th>
            <th>Půjčeno</th>
            <th>Vrátit do</th>
            <th>Stav</th>
            <th></th>
        </tr>
        @forelse ($loans as $loan)
            <tr>
                <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                <td>{{ $loan->reader->name }}</td>
                <td>{{ $loan->borrowed_at->format('d.m.Y') }}</td>
                <td>{{ $loan->due_at->format('d.m.Y') }}</td>
                <td>
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
                        {{-- Tlacitko "Vratit" je formular, ne odkaz: meni data,
                             a to se podle pravidel nesmi delat metodou GET --}}
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
                <td colspan="6" class="muted">Zatím žádné výpůjčky.</td>
            </tr>
        @endforelse
    </table>
@endsection
