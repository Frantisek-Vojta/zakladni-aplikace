@extends('layouts.app')

@section('title', 'Evidence studentů')

@section('content')
    <h1>Evidence studentů</h1>

    <form class="add" method="POST" action="{{ route('students.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Jméno a příjmení" value="{{ old('name') }}" autofocus>
        <input type="text" name="class" placeholder="Třída" value="{{ old('class') }}" style="max-width: 70px">
        <button type="submit">Přidat</button>
    </form>
    @error('name') <p class="error">{{ $message }}</p> @enderror
    @error('class') <p class="error">{{ $message }}</p> @enderror

    <p class="muted">Počet studentů: {{ $students->count() }}</p>

    <ul>
        @forelse ($students as $student)
            <li>
                <span class="text">{{ $student->name }} <span class="muted">({{ $student->class }})</span></span>

                <form method="POST" action="{{ route('students.destroy', $student) }}">
                    @csrf
                    @method('DELETE')
                    <button class="small danger" type="submit">Smazat</button>
                </form>
            </li>
        @empty
            <li class="muted">Zatím žádní studenti.</li>
        @endforelse
    </ul>
@endsection
