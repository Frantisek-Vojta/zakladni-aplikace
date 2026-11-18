<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_are_listed(): void
    {
        Participant::factory()->create(['name' => 'Jan Novák', 'email' => 'jan.novak@example.com']);

        $this->get(route('participants.index'))->assertOk()->assertSee('Jan Novák')->assertSee('jan.novak@example.com');
    }

    public function test_participant_can_be_created(): void
    {
        $this->post(route('participants.store'), ['name' => 'Eva Dvořáková', 'email' => 'eva@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('participants', ['name' => 'Eva Dvořáková', 'email' => 'eva@example.com']);
    }

    public function test_participant_requires_name_and_email(): void
    {
        $this->post(route('participants.store'), [])->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * ZADANI: e-mail ucastnika je platny
     */
    public function test_participant_email_must_be_valid(): void
    {
        $this->post(route('participants.store'), ['name' => 'Jan Novák', 'email' => 'neplatny-email'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('participants', 0);
    }

    /**
     * ZADANI: e-mail ucastnika je jedinecny
     */
    public function test_participant_email_must_be_unique(): void
    {
        Participant::factory()->create(['email' => 'jan.novak@example.com']);

        $this->post(route('participants.store'), ['name' => 'Jiný Jan', 'email' => 'jan.novak@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('participants', 1);
    }

    public function test_existing_participant_can_keep_their_own_email_when_updated(): void
    {
        $participant = Participant::factory()->create(['email' => 'jan.novak@example.com']);

        $this->put(route('participants.update', $participant), [
            'name' => 'Nové jméno',
            'email' => 'jan.novak@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Nové jméno', $participant->fresh()->name);
    }

    public function test_participant_can_be_deleted(): void
    {
        $participant = Participant::factory()->create();

        $this->delete(route('participants.destroy', $participant))->assertRedirect(route('participants.index'));

        $this->assertModelMissing($participant);
    }
}
