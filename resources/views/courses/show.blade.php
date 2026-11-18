@extends('layouts.app')

@section('title', $course->name)

@section('content')
    <h1>{{ $course->name }}</h1>

    <p>
        Kód: <span class="muted">{{ $course->code }}</span><br>
        Začátek: {{ $course->starts_at->format('d.m.Y') }}<br>
        {{-- ZADANI: zobrazeni obsazenosti kurzu --}}
        Obsazenost: <strong>{{ $course->activeEnrollmentsCount() }} / {{ $course->capacity }}</strong>
        @if ($course->isFull())
            <span class="badge overdue">Plno</span>
        @else
            <span class="badge">{{ $course->freeSpots() }} volných míst</span>
        @endif
    </p>

    <p><a href="{{ route('courses.edit', $course) }}">Upravit kurz</a></p>

    <h2>Přihlásit účastníka</h2>

    {{--
        Formular na prihlaseni se zobrazi jen kdyz to ma smysl: musi
        existovat alespon jeden ucastnik a v kurzu musi byt volne misto.
        Je to jen pohodli pro uzivatele - skutecnou kontrolu (vcetne
        dvojiteho prihlaseni) dela EnrollmentController::store(), protoze
        sablone nelze verit.
    --}}
    @if ($participants->isEmpty())
        <p class="muted">Nejdřív přidej účastníka na stránce <a href="{{ route('participants.index') }}">Účastníci</a>.</p>
    @elseif ($course->isFull())
        <p class="muted">Kurz je plně obsazen, další přihlášky nelze přidat.</p>
    @else
        <form class="stack" method="POST" action="{{ route('enrollments.store', $course) }}">
            @csrf

            <label>
                Účastník
                <select name="participant_id">
                    @foreach ($participants as $participant)
                        <option value="{{ $participant->id }}" @selected(old('participant_id') == $participant->id)>
                            {{ $participant->name }} ({{ $participant->email }})
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit">Přihlásit</button>
        </form>

        @error('participant_id') <p class="error">{{ $message }}</p> @enderror
    @endif

    <h2>Seznam přihlášených</h2>

    <table>
        <tr>
            <th>Účastník</th>
            <th>Přihlášen</th>
            <th>Stav</th>
            <th></th>
        </tr>
        @forelse ($enrollments as $enrollment)
            <tr>
                <td>{{ $enrollment->participant->name }}</td>
                <td>{{ $enrollment->enrolled_at->format('d.m.Y') }}</td>
                <td>
                    <span class="badge {{ $enrollment->statusClass() }}">{{ $enrollment->statusLabel() }}</span>
                </td>
                <td>
                    {{-- domaci prace: tlacitka na zmenu stavu, jen u aktivni (enrolled) prihlasky --}}
                    @if ($enrollment->status === 'enrolled')
                        <form method="POST" action="{{ route('enrollments.complete', $enrollment) }}" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button class="small" type="submit">Dokončit</button>
                        </form>
                        <form method="POST" action="{{ route('enrollments.cancel', $enrollment) }}" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button class="small danger" type="submit">Zrušit</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">Na kurz se zatím nikdo nepřihlásil.</td>
            </tr>
        @endforelse
    </table>

    <p><a href="{{ route('courses.index') }}">← Zpět na seznam kurzů</a></p>
@endsection
