<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_are_listed(): void
    {
        Student::factory()->create(['name' => 'Jan Novák', 'class' => '2.B']);

        $this->get(route('students.index'))->assertOk()->assertSee('Jan Novák')->assertSee('2.B');
    }

    public function test_student_can_be_created(): void
    {
        $this->post(route('students.store'), ['name' => 'Eva Dvořáková', 'class' => '3.A'])
            ->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('students', ['name' => 'Eva Dvořáková', 'class' => '3.A']);
    }

    public function test_student_requires_name_and_class(): void
    {
        $this->post(route('students.store'), [])->assertSessionHasErrors(['name', 'class']);
    }

    public function test_student_can_be_deleted(): void
    {
        $student = Student::factory()->create();

        $this->delete(route('students.destroy', $student))->assertRedirect(route('students.index'));

        $this->assertModelMissing($student);
    }
}
