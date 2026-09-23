@extends('layouts.app')

@section('title', 'Seznam úkolů')

@section('content')
    <h1>Seznam úkolů</h1>

    <form class="add" method="POST" action="{{ route('tasks.store') }}">
        @csrf
        <input type="text" name="title" placeholder="Nový úkol…" autofocus>
        <button type="submit">Přidat</button>
    </form>
    @error('title') <p class="error">{{ $message }}</p> @enderror

    <ul>
        @forelse ($tasks as $task)
            <li>
                <span class="text {{ $task->is_done ? 'done' : '' }}">{{ $task->title }}</span>

                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button class="small" type="submit">{{ $task->is_done ? 'Vrátit' : 'Hotovo' }}</button>
                </form>

                <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                    @csrf
                    @method('DELETE')
                    <button class="small danger" type="submit">Smazat</button>
                </form>
            </li>
        @empty
            <li class="muted">Zatím žádné úkoly.</li>
        @endforelse
    </ul>
@endsection
