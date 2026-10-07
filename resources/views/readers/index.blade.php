@extends('layouts.app')

@section('title', 'Čtenáři')

@section('content')
    <h1>Čtenáři</h1>

    <form class="add" method="POST" action="{{ route('readers.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Jméno a příjmení" value="{{ old('name') }}" autofocus>
        <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}">
        <button type="submit">Přidat</button>
    </form>
    @error('name') <p class="error">{{ $message }}</p> @enderror
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <p class="muted">Počet čtenářů: {{ $readers->count() }}</p>

    <ul>
        @forelse ($readers as $reader)
            <li>
                <span class="text">
                    {{ $reader->name }}
                    <span class="muted">({{ $reader->email }})</span>
                    @if ($reader->active_loans_count > 0)
                        <span class="badge">půjčeno: {{ $reader->active_loans_count }}</span>
                    @endif
                </span>

                <form method="POST" action="{{ route('readers.destroy', $reader) }}">
                    @csrf
                    @method('DELETE')
                    <button class="small danger" type="submit">Smazat</button>
                </form>
            </li>
        @empty
            <li class="muted">Zatím žádní čtenáři.</li>
        @endforelse
    </ul>
@endsection
