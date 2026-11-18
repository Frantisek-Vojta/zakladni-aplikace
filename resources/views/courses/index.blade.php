@extends('layouts.app')

@section('title', 'Kurzy')

@section('content')
    <h1>Kurzy</h1>

    {{--
        ZADANI: filtr podle terminu a volnych mist.
        Pouziva metodu GET, takze se filtr objevi v adrese
        (/courses?from=2026-12-01&only_available=1) a jde ho nasdilet.
        GET formular nepotrebuje @csrf - nic nemeni, jen cte.
    --}}
    <form class="add" method="GET" action="{{ route('courses.index') }}">
        <label>
            Od data
            <input type="date" name="from" value="{{ $from }}">
        </label>

        {{-- checked() vraci atribut "checked", kdyz je podminka splnena --}}
        <label>
            <input type="checkbox" name="only_available" value="1" {{ $onlyAvailable ? 'checked' : '' }}>
            Jen s volným místem
        </label>

        <button type="submit">Filtrovat</button>
        @if ($from || $onlyAvailable)
            <a href="{{ route('courses.index') }}"><button class="small" type="button">Zrušit filtr</button></a>
        @endif
    </form>

    <p><a href="{{ route('courses.create') }}">+ Přidat kurz</a></p>

    <table>
        <tr>
            <th>Kód</th>
            <th>Název</th>
            <th>Začátek</th>
            <th>Obsazenost</th>
            <th></th>
        </tr>
        @forelse ($courses as $course)
            <tr>
                <td class="muted">{{ $course->code }}</td>
                <td><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td>
                <td>{{ $course->starts_at->format('d.m.Y') }}</td>
                <td>
                    {{-- active_enrollments_count je spocitany sloupec z withCount() v CourseController::index() --}}
                    {{ $course->active_enrollments_count }} / {{ $course->capacity }}
                    @if ($course->active_enrollments_count >= $course->capacity)
                        <span class="badge overdue">Plno</span>
                    @endif
                </td>
                <td><a href="{{ route('courses.edit', $course) }}">Upravit</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="muted">Žádné kurzy nenalezeny.</td>
            </tr>
        @endforelse
    </table>
@endsection
