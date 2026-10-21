@extends('layouts.app')

@section('title', 'Upravit zařízení')

@section('content')
    <h1>Upravit zařízení</h1>

    <form class="stack" method="POST" action="{{ route('equipment.update', $equipment) }}">
        @csrf
        {{--
            HTML formular umi poslat jen GET a POST. @method('PUT') prida
            skryte policko _method=PUT a Laravel se zachova, jako by prisel PUT -
            diky tomu funguje routa z Route::resource.
        --}}
        @method('PUT')

        <label>
            Název
            {{-- old('name', $equipment->name): prvni hodnota po chybe validace,
                 druha pri prvnim otevreni formulare --}}
            <input type="text" name="name" value="{{ old('name', $equipment->name) }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            Evidenční číslo
            <input type="text" name="inventory_number" value="{{ old('inventory_number', $equipment->inventory_number) }}">
        </label>
        @error('inventory_number') <p class="error">{{ $message }}</p> @enderror

        <label>
            Stav
            <select name="status">
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $equipment->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        @error('status') <p class="error">{{ $message }}</p> @enderror

        <label>
            Datum nákupu
            <input type="date" name="purchased_at" value="{{ old('purchased_at', $equipment->purchased_at->toDateString()) }}">
        </label>
        @error('purchased_at') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit změny</button>
    </form>

    {{-- Smazani je samostatny formular, protoze pouziva metodu DELETE --}}
    <form method="POST" action="{{ route('equipment.destroy', $equipment) }}">
        @csrf
        @method('DELETE')
        <button class="small danger" type="submit">Smazat zařízení</button>
    </form>

    <p><a href="{{ route('equipment.show', $equipment) }}">← Zpět na detail</a></p>
@endsection
