@extends('layouts.app')

@section('title', 'Nové zařízení')

@section('content')
    <h1>Nové zařízení</h1>

    <form class="stack" method="POST" action="{{ route('equipment.store') }}">
        {{-- @csrf = skryty bezpecnostni token, bez nej Laravel vrati chybu 419 --}}
        @csrf

        <label>
            Název
            {{-- old() vrati naposledy napsanou hodnotu, kdyz se formular vratil s chybou --}}
            <input type="text" name="name" value="{{ old('name') }}" autofocus>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            Evidenční číslo
            <input type="text" name="inventory_number" value="{{ old('inventory_number') }}">
        </label>
        @error('inventory_number') <p class="error">{{ $message }}</p> @enderror

        <label>
            Stav
            {{-- nabizime jen povolene stavy, stejny seznam hlida i validace --}}
            <select name="status">
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(old('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        @error('status') <p class="error">{{ $message }}</p> @enderror

        <label>
            Datum nákupu
            <input type="date" name="purchased_at" value="{{ old('purchased_at', now()->toDateString()) }}">
        </label>
        @error('purchased_at') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Uložit</button>
    </form>

    <p><a href="{{ route('equipment.index') }}">← Zpět na seznam</a></p>
@endsection
