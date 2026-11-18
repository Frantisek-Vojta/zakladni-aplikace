<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * KONTROLER UCASTNIKU - kompletni CRUD
 */
class ParticipantController extends Controller
{
    /**
     * READ - seznam ucastniku + u kazdeho pocet aktivnich prihlasek
     */
    public function index(): View
    {
        // withCount() prida ke kazdemu ucastnikovi spocitany sloupec, aniz
        // by tahal z DB cele prihlasky. Vysledek je v
        // $participant->active_enrollments_count.
        $participants = Participant::withCount(['enrollments as active_enrollments_count' => function ($query) {
            $query->where('status', '!=', Enrollment::STATUS_CANCELLED);
        }])->orderBy('name')->get();

        return view('participants.index', ['participants' => $participants]);
    }

    /**
     * CREATE (1/2) - jen zobrazi prazdny formular
     */
    public function create(): View
    {
        return view('participants.create');
    }

    /**
     * CREATE (2/2) - ulozi noveho ucastnika
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $participant = Participant::create($data);

        return redirect()->route('participants.show', $participant);
    }

    /**
     * READ - detail ucastnika se seznamem jeho prihlasek
     */
    public function show(Participant $participant): View
    {
        $enrollments = $participant->enrollments()
            ->with('course')
            ->orderByDesc('enrolled_at')
            ->get();

        return view('participants.show', [
            'participant' => $participant,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * UPDATE (1/2) - formular predplneny soucasnymi hodnotami
     */
    public function edit(Participant $participant): View
    {
        return view('participants.edit', ['participant' => $participant]);
    }

    /**
     * UPDATE (2/2) - ulozi zmeny
     */
    public function update(Request $request, Participant $participant): RedirectResponse
    {
        // ucastnika predame do rules(), aby si pri kontrole jedinecnosti
        // e-mailu "nestezoval sam na sebe" - viz komentar v rules()
        $data = $request->validate($this->rules($participant));

        $participant->update($data);

        return redirect()->route('participants.show', $participant);
    }

    /**
     * DELETE - smaze ucastnika.
     * Jeho prihlasky se smazou same diky cascadeOnDelete() v migraci
     * tabulky enrollments.
     */
    public function destroy(Participant $participant): RedirectResponse
    {
        $participant->delete();

        return redirect()->route('participants.index');
    }

    /**
     * VALIDACNI PRAVIDLA PRO UCASTNIKA
     *
     * $participant je vyplneny jen pri uprave, pri zalozeni noveho je null.
     */
    private function rules(?Participant $participant = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // ZADANI: e-mail ucastnika je platny a jedinecny.
            // email  = musi to byt platny tvar e-mailu
            // unique = jeden e-mail smi byt v tabulce jen jednou
            // ->ignore($participant) pri UPRAVE vynecha ucastnika samotneho
            'email' => ['required', 'email', 'max:255', Rule::unique('participants', 'email')->ignore($participant)],
        ];
    }
}
