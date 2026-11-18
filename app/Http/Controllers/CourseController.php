<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * KONTROLER KURZU - kompletni CRUD + filtr v seznamu
 *
 * CRUD = Create (create + store), Read (index + show),
 *        Update (edit + update), Delete (destroy).
 */
class CourseController extends Controller
{
    /**
     * READ - seznam kurzu + filtr podle terminu a volnych mist
     *
     * ZADANI: "Filtr kurzu podle terminu a volnych mist."
     */
    public function index(Request $request): View
    {
        // /courses?from=2026-12-01            - jen kurzy od tohoto data dal
        // /courses?only_available=1           - jen kurzy, kde je volne misto
        $from = $request->query('from');
        $onlyAvailable = $request->boolean('only_available');

        $courses = Course::query()
            // withCount pricte ke kazdemu kurzu spocitany pocet aktivnich
            // prihlasek, aniz by se musely tahat cele prihlasky do PHP.
            // Vysledek je v $course->active_enrollments_count.
            ->withCount(['enrollments as active_enrollments_count' => function ($query) {
                $query->where('status', '!=', Enrollment::STATUS_CANCELLED);
            }])
            // when() = pridej tuto cast dotazu jen kdyz je podminka splnena
            ->when($from, fn ($query) => $query->startingFrom($from))
            ->orderBy('starts_at')
            ->get()
            // Filtr "jen s volnym mistem" doresujeme az na ziskane kolekci,
            // ne primo v SQL: active_enrollments_count je spocitany sloupec
            // z withCount() a porovnavani takoveho sloupce s jinym sloupcem
            // (capacity) v HAVING podporuje kazda databaze jinak (SQLite
            // pouzivane v testech to bez GROUP BY/agregace odmitne). Kurzu
            // byva v evidenci malo, takze filtr v PHP nic nestoji.
            ->when($onlyAvailable, fn ($courses) => $courses->filter(
                fn (Course $course) => $course->active_enrollments_count < $course->capacity
            ));

        return view('courses.index', [
            'courses' => $courses,
            'from' => $from,
            'onlyAvailable' => $onlyAvailable,
        ]);
    }

    /**
     * CREATE (1/2) - jen zobrazi prazdny formular
     */
    public function create(): View
    {
        return view('courses.create');
    }

    /**
     * CREATE (2/2) - ulozi novy kurz
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $course = Course::create($data);

        return redirect()->route('courses.show', $course);
    }

    /**
     * READ - detail kurzu: obsazenost a seznam prihlasenych
     *
     * ZADANI: "Zobrazeni obsazenosti a seznamu prihlasenych."
     *
     * Course $course = route model binding: z adresy /courses/3 si Laravel
     * sam najde kurz s id 3 (a vrati 404, kdyz neexistuje).
     */
    public function show(Course $course): View
    {
        $enrollments = $course->enrollments()
            // with('participant') resi problem N+1 dotazu - bez toho by se
            // pro KAZDOU prihlasku delal zvlastni dotaz na jejiho ucastnika.
            ->with('participant')
            ->orderByDesc('enrolled_at')
            ->get();

        return view('courses.show', [
            'course' => $course,
            'enrollments' => $enrollments,
            // ucastnici posilame do rozbalovaciho seznamu formulare prihlaseni
            'participants' => Participant::orderBy('name')->get(),
        ]);
    }

    /**
     * UPDATE (1/2) - formular predplneny soucasnymi hodnotami
     */
    public function edit(Course $course): View
    {
        return view('courses.edit', ['course' => $course]);
    }

    /**
     * UPDATE (2/2) - ulozi zmeny
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        // kurz predame do rules(), aby si pri kontrole jedinecnosti kodu
        // "nestezoval sam na sebe" - viz komentar v rules()
        $data = $request->validate($this->rules($course));

        $course->update($data);

        return redirect()->route('courses.show', $course);
    }

    /**
     * DELETE - smaze kurz.
     * Prihlasky na tento kurz se smazou same diky cascadeOnDelete()
     * nastavenemu v migraci tabulky enrollments.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('courses.index');
    }

    /**
     * VALIDACNI PRAVIDLA PRO KURZ
     *
     * V jedne metode, aby se neopakovala ve store() i update().
     * $course je vyplnene jen pri uprave, pri zakladani noveho kurzu je null.
     */
    private function rules(?Course $course = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // ZADANI: kod kurzu je jedinecny.
            // ->ignore($course) pri UPRAVE vynecha kurz samotny - bez toho
            // by sel kurz ulozit jen po zmene kodu (jeho vlastni kod uz je
            // v tabulce a unique by ho oznacil za duplikat).
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course)],

            // ZADANI: kapacita je cele cislo vetsi nez 0 (min:1, ne min:0)
            'capacity' => ['required', 'integer', 'min:1'],

            'starts_at' => ['required', 'date'],
        ];
    }
}
