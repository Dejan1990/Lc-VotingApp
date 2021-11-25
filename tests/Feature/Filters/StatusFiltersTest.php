<?php

namespace Tests\Feature;

use App\Http\Livewire\IdeasIndex;
use App\Http\Livewire\SetStatus;
use App\Http\Livewire\StatusFilters;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Category;
use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class StatusFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function index_page_contains_status_filters_livewire_component()
    {
        $this->get(route('idea.index'))
            ->assertSeeLivewire('status-filters');
    }

    /** @test */
    public function show_page_contains_status_filters_livewire_component()
    {
        $idea = Idea::factory()->create();
        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire(StatusFilters::class);
    } 

    /** @test */
    public function shows_correct_status_count()
    {
        $statusClosed = Status::factory()->create(['id' => 5, 'name' => 'Closed']);
        $statusConsidering = Status::factory()->create(['id' => 2, 'name' => 'Considering']);

        Idea::factory()->create(['status_id' => $statusClosed->id]);
        Idea::factory()->create(['status_id' => $statusClosed->id]);
        Idea::factory()->create(['status_id' => $statusConsidering->id]);

        Livewire::test(StatusFilters::class)
            ->assertSee('All Ideas (3)')
            ->assertSee('Closed (2)')
            ->assertSee('Considering (1)');
    }

    /** @test */
    public function filtering_works_when_query_string_in_place()
    // checking if the query string actially filter the statuses
    {
        $statusClosed = Status::factory()->create(['name' => 'Closed']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);

        Idea::factory()->create(['status_id' => $statusClosed->id]);
        Idea::factory()->create(['status_id' => $statusClosed->id]);
        Idea::factory()->create(['status_id' => $statusClosed->id]);
        Idea::factory()->create(['status_id' => $statusConsidering->id]);
        Idea::factory()->create(['status_id' => $statusConsidering->id]);

        Livewire::withQueryParams(['status' => 'Closed'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->status->name === 'Closed';
            });
    }

    /** @test */
    public function show_page_does_not_show_selected_status()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSee('text-gray-900 border-blue');
    }

    /** @test */
    public function index_page_shows_selected_status()
    {
        $this->get(route('idea.index'))
            ->assertSee('text-gray-900 border-blue');
    }
}
