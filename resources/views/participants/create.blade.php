@extends('layouts.app')

@section('title', 'Nový účastník')

@section('content')
    <h1>Nový účastník</h1>

    <form class="stack" method="POST" action="{{ route('participants.store') }}">
        @csrf

        <label>
            Jméno
            <input type="text" name="name" value="{{ old('name') }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email') }}">
        </label>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit</button>
    </form>

    <p><a href="{{ route('participants.index') }}">← Zpět na seznam</a></p>
@endsection
