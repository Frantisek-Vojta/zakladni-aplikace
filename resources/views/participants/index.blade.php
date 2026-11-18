@extends('layouts.app')

@section('title', 'Účastníci')

@section('content')
    <h1>Účastníci</h1>

    <p><a href="{{ route('participants.create') }}">+ Přidat účastníka</a></p>

    <table>
        <tr>
            <th>Jméno</th>
            <th>E-mail</th>
            <th>Aktivní přihlášky</th>
            <th></th>
        </tr>
        @forelse ($participants as $participant)
            <tr>
                <td><a href="{{ route('participants.show', $participant) }}">{{ $participant->name }}</a></td>
                <td class="muted">{{ $participant->email }}</td>
                {{-- active_enrollments_count je spocitany sloupec z withCount() v ParticipantController::index() --}}
                <td>{{ $participant->active_enrollments_count }}</td>
                <td><a href="{{ route('participants.edit', $participant) }}">Upravit</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">Zatím žádní účastníci.</td>
            </tr>
        @endforelse
    </table>
@endsection
