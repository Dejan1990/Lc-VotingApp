<?php

namespace Tests\Feature;

use App\Http\Livewire\IdeasIndex;
use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Vote;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class OtherFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function top_voted_filter_works()
    {
       Idea::factory()->hasVotes(5)->create();
        Idea::factory()->hasVotes(3)->create();

        Livewire::test(IdeasIndex::class)
            ->set('filter', 'Top Voted')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                && $ideas->first()->votes()->count() === 5
                && $ideas->get(1)->votes()->count() === 3;
            });
    }

    /** @test */
    public function my_ideas_filter_works_correctly_when_user_logged_in()
    {
        $user = User::factory()->hasIdeas(2)->create();
        $userB = User::factory()->hasIdeas()->create();

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'My Ideas')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->id === 2
                    && $ideas->get(1)->id === 1;
            });
    }

    /** @test */
    public function my_ideas_filter_works_correctly_when_user_is_not_logged_in()
    {
        Livewire::test(IdeasIndex::class)
            ->set('filter', 'My Ideas')
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function my_ideas_filter_works_correctly_with_categories_filter()
    {
        $user = User::factory()->create();

        Idea::factory(2)->forCategory(['name' => 'Category 1'])->create(['user_id' => $user->id]);
        Idea::factory()->forCategory(['name' => 'Category 2'])->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'My Ideas')
            ->set('category', 'Category 1')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->id === 2
                    && $ideas->get(1)->id === 1;
            });
    }

    /** @test */
    public function no_filters_works_correctly()
    {
       Idea::factory(5)->create();

        Livewire::test(IdeasIndex::class)
            ->set('filter', '')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 5
                    && $ideas->first()->id === 5
                    && $ideas->last()->id === 1;
            });
    }

    /** @test */
    public function spam_ideas_filter_works()
    {
        $user = User::factory()->admin()->create();

        Idea::factory()->create(['spam_reports' => 1]);
        Idea::factory()->create(['spam_reports' => 5]);
        Idea::factory()->create(['spam_reports' => 3]);
        Idea::factory()->create();

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'Spam Ideas')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->id === 2
                    && $ideas->get(1)->id === 3
                    && $ideas->get(2)->id === 1;
            });
    }

    /** @test */
    public function spam_comments_filter_works()
    {
        $user = User::factory()->admin()->create();

        Idea::factory()->hasComments(['spam_reports' => 3])->create();
        Idea::factory()->hasComments(['spam_reports' => 5])->create();
        Idea::factory()->hasComments()->create();

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'Spam Comments')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2;
            });
    }
}
