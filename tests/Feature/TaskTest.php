<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_are_listed(): void
    {
        Task::factory()->create(['title' => 'Nakoupit']);

        $this->get(route('tasks.index'))->assertOk()->assertSee('Nakoupit');
    }

    public function test_task_can_be_created(): void
    {
        $this->post(route('tasks.store'), ['title' => 'Uklidit'])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['title' => 'Uklidit', 'is_done' => false]);
    }

    public function test_task_title_is_required(): void
    {
        $this->post(route('tasks.store'), ['title' => ''])->assertSessionHasErrors('title');
    }

    public function test_task_can_be_toggled(): void
    {
        $task = Task::factory()->create();

        $this->patch(route('tasks.toggle', $task))->assertRedirect(route('tasks.index'));

        $this->assertTrue($task->fresh()->is_done);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->create();

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));

        $this->assertModelMissing($task);
    }
}
