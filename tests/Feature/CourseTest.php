<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    private function courseData(array $data = []): array
    {
        return array_merge([
            'name' => 'Základy PHP',
            'code' => 'PHP-101',
            'capacity' => 10,
            'starts_at' => now()->addMonth()->toDateString(),
        ], $data);
    }

    public function test_courses_are_listed(): void
    {
        Course::factory()->create(['name' => 'Základy PHP', 'code' => 'PHP-101']);

        $this->get(route('courses.index'))->assertOk()->assertSee('Základy PHP')->assertSee('PHP-101');
    }

    /**
     * ZADANI: "Na konci musí automatická kontrola vytvořit kurz a ověřit
     * jeho uložení." - presne tohle tento test dela.
     */
    public function test_course_can_be_created_and_is_stored(): void
    {
        $this->post(route('courses.store'), $this->courseData())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courses', [
            'name' => 'Základy PHP',
            'code' => 'PHP-101',
            'capacity' => 10,
        ]);
    }

    public function test_course_requires_name_code_capacity_and_start_date(): void
    {
        $this->post(route('courses.store'), [])
            ->assertSessionHasErrors(['name', 'code', 'capacity', 'starts_at']);
    }

    /**
     * ZADANI: kod kurzu je jedinecny
     */
    public function test_course_code_must_be_unique(): void
    {
        Course::factory()->create(['code' => 'PHP-101']);

        $this->post(route('courses.store'), $this->courseData(['code' => 'PHP-101']))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('courses', 1);
    }

    /**
     * ZADANI: kapacita je cele cislo vetsi nez 0
     */
    public function test_course_capacity_must_be_a_positive_integer(): void
    {
        $this->post(route('courses.store'), $this->courseData(['capacity' => 0]))
            ->assertSessionHasErrors('capacity');

        $this->post(route('courses.store'), $this->courseData(['capacity' => -5]))
            ->assertSessionHasErrors('capacity');

        $this->post(route('courses.store'), $this->courseData(['capacity' => 'deset']))
            ->assertSessionHasErrors('capacity');

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_existing_course_can_keep_its_own_code_when_updated(): void
    {
        $course = Course::factory()->create(['code' => 'PHP-101']);

        $this->put(route('courses.update', $course), $this->courseData(['code' => 'PHP-101', 'name' => 'Nový název']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Nový název', $course->fresh()->name);
    }

    public function test_course_can_be_deleted(): void
    {
        $course = Course::factory()->create();

        $this->delete(route('courses.destroy', $course))->assertRedirect(route('courses.index'));

        $this->assertModelMissing($course);
    }

    /**
     * ZADANI: filtr kurzu podle terminu
     */
    public function test_courses_can_be_filtered_by_start_date(): void
    {
        $brzy = Course::factory()->create(['name' => 'Brzký kurz', 'starts_at' => now()->addDays(2)]);
        $pozdni = Course::factory()->create(['name' => 'Pozdní kurz', 'starts_at' => now()->addMonths(6)]);

        $response = $this->get(route('courses.index', ['from' => now()->addMonth()->toDateString()]));

        $response->assertOk()->assertSee('Pozdní kurz')->assertDontSee('Brzký kurz');
    }

    /**
     * ZADANI: filtr kurzu podle volnych mist
     */
    public function test_courses_can_be_filtered_by_free_spots(): void
    {
        $plny = Course::factory()->create(['name' => 'Plný kurz', 'capacity' => 1]);
        $volny = Course::factory()->create(['name' => 'Volný kurz', 'capacity' => 5]);

        $participant = Participant::factory()->create();
        $this->post(route('enrollments.store', $plny), ['participant_id' => $participant->id]);

        $response = $this->get(route('courses.index', ['only_available' => 1]));

        $response->assertOk()->assertSee('Volný kurz')->assertDontSee('Plný kurz');
    }
}
