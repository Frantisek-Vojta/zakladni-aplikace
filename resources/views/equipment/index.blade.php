@extends('layouts.app')

@section('title', 'Evidence techniky')

@section('content')
    <h1>Evidence techniky</h1>

    {{--
        FILTR PODLE STAVU.
        Jsou to obycejne odkazy (GET), takze stav je videt v adrese
        /equipment?status=service a odkaz se da nasdilet.
        Trida "on" zvyrazni prave zvoleny filtr.
    --}}
    <div class="filters">
        <a href="{{ route('equipment.index') }}" class="{{ $status ? '' : 'on' }}">Vše</a>
        {{-- $statuses je Equipment::STATUSES, tedy ['active' => 'V provozu', ...] --}}
        @foreach ($statuses as $key => $label)
            <a href="{{ route('equipment.index', ['status' => $key]) }}"
               class="{{ $status === $key ? 'on' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <p><a href="{{ route('equipment.create') }}">+ Přidat zařízení</a></p>

    <table>
        <tr>
            <th>Název</th>
            <th>Evidenční číslo</th>
            <th>Stav</th>
            <th>Nákup</th>
            <th class="num">Servis celkem</th>
            <th></th>
        </tr>
        @forelse ($equipment as $item)
            <tr>
                <td><a href="{{ route('equipment.show', $item) }}">{{ $item->name }}</a></td>
                <td class="muted">{{ $item->inventory_number }}</td>
                <td>
                    {{-- stav je odlisen barvou (CSS trida) i textem (cesky popis) --}}
                    <span class="badge {{ $item->statusClass() }}">{{ $item->statusLabel() }}</span>
                </td>
                <td>{{ $item->purchased_at->format('d.m.Y') }}</td>
                <td class="num">{{ number_format($item->totalServiceCost(), 2, ',', ' ') }} Kč</td>
                <td><a href="{{ route('equipment.edit', $item) }}">Upravit</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted">
                    @if ($status)
                        V tomto stavu není žádné zařízení.
                    @else
                        Zatím žádná zařízení.
                    @endif
                </td>
            </tr>
        @endforelse
    </table>
@endsection
