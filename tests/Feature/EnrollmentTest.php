<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy prihlasovani ucastniku a hlidani kapacity + stavu prihlasky
 * (enrolled / cancelled / completed) - domaci prace ze zadani.
 */
class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $data = []): Course
    {
        return Course::factory()->create($data);
    }

    private function participant(array $data = []): Participant
    {
        return Participant::factory()->create($data);
    }

    public function test_participant_can_enroll_into_a_course(): void
    {
        $course = $this->course(['capacity' => 2]);
        $participant = $this->participant();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'participant_id' => $participant->id,
            'status' => Enrollment::STATUS_ENROLLED,
        ]);
    }

    /**
     * ZADANI: ucastnik se nesmi prihlasit dvakrat
     */
    public function test_participant_cannot_enroll_into_the_same_course_twice(): void
    {
        $course = $this->course(['capacity' => 5]);
        $participant = $this->participant();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasNoErrors();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasErrors('participant_id');

        $this->assertDatabaseCount('enrollments', 1);
    }

    /**
     * ZADANI: pocet aktivnich prihlasek nesmi prekrocit kapacitu
     */
    public function test_enrollment_is_rejected_when_course_is_full(): void
    {
        $course = $this->course(['capacity' => 1]);
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id]);

        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id])
            ->assertSessionHasErrors('participant_id');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_different_participants_can_enroll_up_to_capacity(): void
    {
        $course = $this->course(['capacity' => 2]);

        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id])
            ->assertSessionHasNoErrors();
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('enrollments', 2);
        $this->assertTrue($course->fresh()->isFull());
    }

    public function test_enrollment_requires_an_existing_participant(): void
    {
        $course = $this->course();

        $this->post(route('enrollments.store', $course), ['participant_id' => 999])
            ->assertSessionHasErrors('participant_id');

        $this->assertDatabaseCount('enrollments', 0);
    }

    /**
     * ZADANI (domaci prace): stav "cancelled" uvolni misto v kapacite
     */
    public function test_cancelling_an_enrollment_frees_a_spot(): void
    {
        $course = $this->course(['capacity' => 1]);
        $participant = $this->participant();
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);
        $enrollment = Enrollment::first();

        $this->patch(route('enrollments.cancel', $enrollment));

        $this->assertSame(Enrollment::STATUS_CANCELLED, $enrollment->fresh()->status);
        $this->assertFalse($course->fresh()->isFull());
        $this->assertSame(0, $course->fresh()->activeEnrollmentsCount());
    }

    public function test_participant_can_enroll_again_after_cancelling(): void
    {
        $course = $this->course(['capacity' => 1]);
        $participant = $this->participant();
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);
        $this->patch(route('enrollments.cancel', Enrollment::first()));

        // po zruseni uz pravidlo "dvojite prihlaseni" neplati
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('enrollments', 2);
    }

    /**
     * ZADANI (domaci prace): stav "completed"
     */
    public function test_enrollment_can_be_marked_as_completed(): void
    {
        $course = $this->course();
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id]);
        $enrollment = Enrollment::first();

        $this->patch(route('enrollments.complete', $enrollment));

        $this->assertSame(Enrollment::STATUS_COMPLETED, $enrollment->fresh()->status);
    }

    /**
     * Dokoncena prihlaska stale "zabira" misto v kapacite - ucastnik
     * kurz absolvoval, nejde o neplatny zaznam jako u zruseni.
     */
    public function test_completed_enrollment_still_counts_towards_capacity(): void
    {
        $course = $this->course(['capacity' => 1]);
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id]);
        $this->patch(route('enrollments.complete', Enrollment::first()));

        $this->assertTrue($course->fresh()->isFull());
    }

    public function test_completed_enrollment_cannot_be_cancelled(): void
    {
        $course = $this->course();
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id]);
        $enrollment = Enrollment::first();
        $this->patch(route('enrollments.complete', $enrollment));

        $this->patch(route('enrollments.cancel', $enrollment));

        // zustava "completed" - dokoncenou prihlasku uz nejde zrusit
        $this->assertSame(Enrollment::STATUS_COMPLETED, $enrollment->fresh()->status);
    }

    /**
     * ZADANI: zobrazeni obsazenosti a seznamu prihlasenych
     */
    public function test_course_detail_shows_occupancy_and_enrolled_participants(): void
    {
        $course = $this->course(['capacity' => 3]);
        $participant = $this->participant(['name' => 'Karel Účastník']);
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Karel Účastník')
            ->assertSee('1 / 3');
    }

    public function test_deleting_a_course_deletes_its_enrollments(): void
    {
        $course = $this->course();
        $this->post(route('enrollments.store', $course), ['participant_id' => $this->participant()->id]);

        $this->delete(route('courses.destroy', $course));

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_deleting_a_participant_deletes_their_enrollments(): void
    {
        $participant = $this->participant();
        $this->post(route('enrollments.store', $this->course()), ['participant_id' => $participant->id]);

        $this->delete(route('participants.destroy', $participant));

        $this->assertDatabaseCount('enrollments', 0);
    }
}
