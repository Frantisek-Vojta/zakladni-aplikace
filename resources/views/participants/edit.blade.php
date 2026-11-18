@extends('layouts.app')

@section('title', 'Upravit účastníka')

@section('content')
    <h1>Upravit účastníka</h1>

    <form class="stack" method="POST" action="{{ route('participants.update', $participant) }}">
        @csrf
        @method('PUT')

        <label>
            Jméno
            <input type="text" name="name" value="{{ old('name', $participant->name) }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email', $participant->email) }}">
        </label>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit změny</button>
    </form>

    {{-- Smazani je samostatny formular, protoze pouziva metodu DELETE --}}
    <form method="POST" action="{{ route('participants.destroy', $participant) }}">
        @csrf
        @method('DELETE')
        <button class="small danger" type="submit">Smazat účastníka</button>
    </form>

    <p><a href="{{ route('participants.show', $participant) }}">← Zpět na detail</a></p>
@endsection
