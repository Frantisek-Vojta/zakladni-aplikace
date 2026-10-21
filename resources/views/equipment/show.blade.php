@extends('layouts.app')

@section('title', $equipment->name)

@section('content')
    <h1>
        {{ $equipment->name }}
        {{-- stav odlisen barvou i textem --}}
        <span class="badge {{ $equipment->statusClass() }}">{{ $equipment->statusLabel() }}</span>
    </h1>

    <p>
        Evidenční číslo: <span class="muted">{{ $equipment->inventory_number }}</span><br>
        Datum nákupu: {{ $equipment->purchased_at->format('d.m.Y') }}
    </p>

    <p><a href="{{ route('equipment.edit', $equipment) }}">Upravit zařízení</a></p>

    <h2>Nový servisní záznam</h2>

    {{--
        Formular posila na /equipment/{id}/service-records - zarizeni je
        soucasti adresy, takze ho nejde podvrhnout skrytym polickem.
    --}}
    <form class="stack" method="POST" action="{{ route('service-records.store', $equipment) }}">
        @csrf

        <label>
            Popis zásahu
            <input type="text" name="description" value="{{ old('description') }}" placeholder="Výměna baterie">
        </label>
        @error('description') <p class="error">{{ $message }}</p> @enderror

        <label>
            Datum servisu
            <input type="date" name="serviced_at" value="{{ old('serviced_at', now()->toDateString()) }}">
        </label>
        @error('serviced_at') <p class="error">{{ $message }}</p> @enderror

        <label>
            Cena (Kč)
            {{-- step="0.01" dovoli halere, min="0" nepusti zapornou cenu.
                 Je to jen pomoc prohlizece - skutecnou kontrolu dela validace
                 na serveru ('numeric', 'min:0'), protoze HTML jde obejit. --}}
            <input type="number" name="cost" step="0.01" min="0" value="{{ old('cost') }}">
        </label>
        @error('cost') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Přidat záznam</button>
    </form>

    <h2>Servisní historie</h2>

    <table>
        <tr>
            <th>Datum</th>
            <th>Popis</th>
            <th class="num">Cena</th>
            <th></th>
        </tr>
        @forelse ($records as $record)
            <tr>
                <td>{{ $record->serviced_at->format('d.m.Y') }}</td>
                <td>{{ $record->description }}</td>
                {{-- number_format: 1234.5 -> "1 234,50" (ceska desetinna carka) --}}
                <td class="num">{{ number_format($record->cost, 2, ',', ' ') }} Kč</td>
                <td>
                    <form method="POST" action="{{ route('service-records.destroy', $record) }}">
                        @csrf
                        @method('DELETE')
                        <button class="small danger" type="submit">Smazat</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">Zařízení zatím nebylo v servisu.</td>
            </tr>
        @endforelse

        {{-- SOUCET SERVISNICH NAKLADU - pocita Equipment::totalServiceCost() --}}
        <tr>
            <td colspan="2" class="total">Servisní náklady celkem</td>
            <td class="num total">{{ number_format($totalCost, 2, ',', ' ') }} Kč</td>
            <td></td>
        </tr>
    </table>

    @if ($records->isNotEmpty())
        {{-- volitelne rozsireni ze zadani: export historie do CSV --}}
        <p><a href="{{ route('equipment.export', $equipment) }}">⤓ Exportovat historii do CSV</a></p>
    @endif

    <p><a href="{{ route('equipment.index') }}">← Zpět na seznam</a></p>
@endsection
