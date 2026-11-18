@extends('layouts.app')

@section('title', 'Upravit kurz')

@section('content')
    <h1>Upravit kurz</h1>

    <form class="stack" method="POST" action="{{ route('courses.update', $course) }}">
        @csrf
        @method('PUT')

        <label>
            Název
            <input type="text" name="name" value="{{ old('name', $course->name) }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            Kód kurzu
            <input type="text" name="code" value="{{ old('code', $course->code) }}">
        </label>
        @error('code') <p class="error">{{ $message }}</p> @enderror

        <label>
            Kapacita
            <input type="number" name="capacity" min="1" value="{{ old('capacity', $course->capacity) }}">
        </label>
        @error('capacity') <p class="error">{{ $message }}</p> @enderror

        <label>
            Datum začátku
            <input type="date" name="starts_at" value="{{ old('starts_at', $course->starts_at->toDateString()) }}">
        </label>
        @error('starts_at') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit změny</button>
    </form>

    {{-- Smazani je samostatny formular, protoze pouziva metodu DELETE --}}
    <form method="POST" action="{{ route('courses.destroy', $course) }}">
        @csrf
        @method('DELETE')
        <button class="small danger" type="submit">Smazat kurz</button>
    </form>

    <p><a href="{{ route('courses.show', $course) }}">← Zpět na detail</a></p>
@endsection
