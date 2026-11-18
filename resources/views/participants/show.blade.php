@extends('layouts.app')

@section('title', $participant->name)

@section('content')
    <h1>{{ $participant->name }}</h1>

    <p>E-mail: <span class="muted">{{ $participant->email }}</span></p>

    <p><a href="{{ route('participants.edit', $participant) }}">Upravit účastníka</a></p>

    <h2>Přihlášky na kurzy</h2>

    <table>
        <tr>
            <th>Kurz</th>
            <th>Přihlášen</th>
            <th>Stav</th>
        </tr>
        @forelse ($enrollments as $enrollment)
            <tr>
                <td><a href="{{ route('courses.show', $enrollment->course) }}">{{ $enrollment->course->name }}</a></td>
                <td>{{ $enrollment->enrolled_at->format('d.m.Y') }}</td>
                <td>
                    <span class="badge {{ $enrollment->statusClass() }}">{{ $enrollment->statusLabel() }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="muted">Zatím se nepřihlásil na žádný kurz.</td>
            </tr>
        @endforelse
    </table>

    <p><a href="{{ route('participants.index') }}">← Zpět na seznam účastníků</a></p>
@endsection
