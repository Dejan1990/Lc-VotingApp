<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Status;
use Livewire\Livewire;
use PHPUnit\Framework\Test;
use App\Http\Livewire\SetStatus;
use App\Jobs\NotifyAllVoters;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSetStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function show_page_contains_set_status_livewire_component_when_user_is_admin()
    {
        $user = User::factory()->admin()->create();
        $idea = Idea::factory()->create();

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire(SetStatus::class);
    }

    /** @test */
    public function show_page_does_notcontain_set_status_livewire_component_when_user_is_not_admin()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('set-status');
    }

    /** @test */
    public function initial_status_is_set_correctly()
    {
        $user = User::factory()->admin()->create();
        $statusConsidering = Status::factory()->create();
        $idea = Idea::factory()->create(['status_id' => $statusConsidering->id]);

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea
            ])
            ->assertSet('status', $statusConsidering->id);
    }

    /** @test */
    public function can_set_status_correctly_no_comment()
    {
        $user = User::factory()->admin()->create();
        $statusConsidering = Status::factory()->create();
        $statusInProgress = Status::factory()->create();

        $idea = Idea::factory()->create([
            'status_id' => $statusInProgress->id
        ]);

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea
            ])
            ->set('status', $statusConsidering->id)
            ->call('setStatus')
            ->assertEmitted('statusWasUpdated');

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status_id' => $statusConsidering->id
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'No comment was added.',
            'is_status_update' => true,
        ]);
    }

    /** @test */
    public function can_set_status_correctly_with_comment()
    {
        $user = User::factory()->admin()->create();
        $statusConsidering = Status::factory()->create();
        $statusInProgress = Status::factory()->create();

        $idea = Idea::factory()->create([
            'status_id' => $statusInProgress->id
        ]);

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea
            ])
            ->set('status', $statusConsidering->id)
            ->set('comment', 'My first comment')
            ->call('setStatus')
            ->assertEmitted('statusWasUpdated');

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status_id' => $statusConsidering->id
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'My first comment',
            'is_status_update' => true
        ]);
    }

    /** @test */
    public function can_set_status_correctly_while_notifying_all_voters()
    {
        $user = User::factory()->admin()->create();

        $statusConsidering = Status::factory()->create();
        $statusInProgress = Status::factory()->create();

        $idea = Idea::factory()->create([
            'status_id' => $statusConsidering->id
        ]);

        Queue::fake();
        Queue::assertNothingPushed();

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea
            ])
            ->set('status', $statusInProgress->id)
            ->set('notifyAllVoters', true)
            ->call('setStatus')
            ->assertEmitted('statusWasUpdated');

        Queue::assertPushed(NotifyAllVoters::class);
    }
}
