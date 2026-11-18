<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * KONTROLER PRIHLASEK
 *
 * Tady je nejzajimavejsi logika cele aplikace - prihlaseni ucastnika
 * do kurzu a hlidani kapacity. Krome formalni validace (existuje ucastnik?)
 * resime dve pravidla, ktera se beznym validacnim pravidlem napsat nedaji,
 * protoze se musi zjistovat dotazem do databaze:
 *
 *   1) ucastnik se nesmi prihlasit dvakrat do stejneho kurzu
 *   2) pocet aktivnich prihlasek nesmi prekrocit kapacitu kurzu
 */
class EnrollmentController extends Controller
{
    /**
     * PRIHLASENI UCASTNIKA DO KURZU
     *
     * Postup: 1) zvalidovat, ze ucastnik existuje
     *         2) overit, ze se tento ucastnik do kurzu jeste neprihlasil
     *         3) overit, ze v kurzu je volne misto
     *         4) ulozit prihlasku se stavem "enrolled"
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            // exists = v tabulce participants takove id opravdu existuje
            // (brani ulozeni prihlasky na neexistujiciho ucastnika)
            'participant_id' => ['required', 'exists:participants,id'],
        ]);

        // ZADANI: ucastnik se nesmi prihlasit dvakrat.
        //
        // Hledame AKTIVNI prihlasku (viz scope active() v modelu Enrollment)
        // se stejnym kurzem I stejnym ucastnikem. Zruseni prihlasky
        // (status 'cancelled') tedy umoznuje prihlasit se znovu.
        //
        // Proc to neni validacni pravidlo? Protoze se to nepozna z odeslanych
        // dat, ale az dotazem na databazi. Proto obycejny if a rucni vraceni
        // chyby - back() + withErrors() + withInput() stejne jako u vypujcek.
        $alreadyEnrolled = $course->enrollments()
            ->where('participant_id', $data['participant_id'])
            ->active()
            ->exists();

        if ($alreadyEnrolled) {
            return back()
                ->withErrors(['participant_id' => 'Tento účastník je do kurzu už přihlášen.'])
                ->withInput();
        }

        // ZADANI: pocet aktivnich prihlasek nesmi prekrocit kapacitu kurzu.
        if ($course->isFull()) {
            return back()
                ->withErrors(['participant_id' => 'Kurz je již plně obsazen.'])
                ->withInput();
        }

        $course->enrollments()->create([
            'participant_id' => $data['participant_id'],
            'enrolled_at' => now(),
            'status' => Enrollment::STATUS_ENROLLED,
        ]);

        return redirect()->route('courses.show', $course);
    }

    /**
     * ZRUSENI PRIHLASKY (domaci prace - stav "cancelled")
     *
     * Prihlasku nemazeme, jen zmenime jeji stav - zustane v historii
     * a uvolni se misto v kapacite kurzu (activeEnrollmentsCount() stavy
     *'cancelled' nepocita).
     */
    public function cancel(Enrollment $enrollment): RedirectResponse
    {
        // Jde zrusit jen aktivni (dosud nezrusenou) prihlasku - dokoncenou
        // prihlasku uz zrusit nejde, kurz uz probehl.
        if ($enrollment->status === Enrollment::STATUS_ENROLLED) {
            $enrollment->update(['status' => Enrollment::STATUS_CANCELLED]);
        }

        // back() = zustan na strance, odkud uzivatel kliknul
        return back();
    }

    /**
     * DOKONCENI PRIHLASKY (domaci prace - stav "completed")
     *
     * Oznaci, ze ucastnik kurz absolvoval. Jde udelat jen z aktivniho
     * stavu "enrolled" - zrusenou prihlasku uz nejde dodatecne dokoncit.
     */
    public function complete(Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->status === Enrollment::STATUS_ENROLLED) {
            $enrollment->update(['status' => Enrollment::STATUS_COMPLETED]);
        }

        return back();
    }
}
