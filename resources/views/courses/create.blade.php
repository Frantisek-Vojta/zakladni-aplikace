@extends('layouts.app')

@section('title', 'Nový kurz')

@section('content')
    <h1>Nový kurz</h1>

    <form class="stack" method="POST" action="{{ route('courses.store') }}">
        @csrf

        <label>
            Název
            <input type="text" name="name" value="{{ old('name') }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            Kód kurzu
            <input type="text" name="code" value="{{ old('code') }}" placeholder="např. PHP-101">
        </label>
        @error('code') <p class="error">{{ $message }}</p> @enderror

        <label>
            Kapacita
            <input type="number" name="capacity" min="1" value="{{ old('capacity', 10) }}">
        </label>
        @error('capacity') <p class="error">{{ $message }}</p> @enderror

        <label>
            Datum začátku
            <input type="date" name="starts_at" value="{{ old('starts_at') }}">
        </label>
        @error('starts_at') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit</button>
    </form>

    <p><a href="{{ route('courses.index') }}">← Zpět na seznam</a></p>
@endsection
