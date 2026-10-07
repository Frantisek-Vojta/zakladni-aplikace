<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReaderController extends Controller
{
    /**
     * seznam ctenaru
     */
    public function index(): View
    {
        // withCount spocita pocet nevracenych vypujcek ke kazdemu ctenari
        $readers = Reader::withCount(['loans as active_loans_count' => function ($query) {
            $query->whereNull('returned_at');
        }])->orderBy('name')->get();

        return view('readers.index', ['readers' => $readers]);
    }

    /**
     * ulozi ctenare
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('readers', 'email')],
        ]);

        Reader::create($data);

        return redirect()->route('readers.index');
    }

    /**
     * smaze ctenare (i s jeho vypujckami)
     */
    public function destroy(Reader $reader): RedirectResponse
    {
        $reader->delete();

        return redirect()->route('readers.index');
    }
}
