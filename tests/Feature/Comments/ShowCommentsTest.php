<?php

namespace Tests\Feature\Comments;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowCommentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function idea_comments_livewire_component_renders()
    {
        $idea = Idea::factory()->create();
        Comment::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('idea-comments');
    }

    /** @test */
    public function idea_comment_livewire_component_renders()
    {
        $idea = Idea::factory()->hasComments()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('idea-comment');
    }

    /** @test */
    public function no_comments_shows_appropriate_message()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSee('No comments yet...');
    }

    /** @test */
    public function list_of_comments_shows_on_idea_page()
    {
        /*$idea = Idea::factory()->hasComments(5)->create();
        
        $this->get(route('idea.show', $idea))
            ->assertViewHas('idea', function ($idea) {
                return $idea->comments->count() === 5
                    && $idea->comments->first()->id === 1
                    && $idea->comments->get(2)->id === 3
                    && $idea->comments->last()->id === 5;
            })
            ->assertSee('5 comments');*/

        $idea = Idea::factory()->hasComments(5)->create();

        $this->get(route('idea.show', $idea))
            ->assertSee('5 comments');
        
        $this->assertTrue($idea->comments->first()->id === 1);
        $this->assertFalse($idea->comments->first()->id === 5);
        $this->assertTrue($idea->comments->get(1)->id === 2);
        $this->assertTrue($idea->comments->last()->id === 5);
    }

    /** @test */
    public function comments_count_shows_correctly_on_index_page()
    {
        Idea::factory()->hasComments(5)->create();
        //Comment::factory()->count(5)->forIdea()->create();

        $this->get('/')
            ->assertSee('5 comments');
    }

    /** @test */
    public function op_badge_shows_if_author_of_idea_comments_on_idea()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $user->id]);

        Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id
        ]);

        $this->get(route('idea.show', $idea))
            ->assertSee('OP');
    }

    /** @test */
    public function comments_pagination_works()
    {
        $idea = Idea::factory()->create();

        $commentOne = Comment::factory()->create([
            'idea_id' => $idea
        ]);

        Comment::factory($commentOne->getPerPage())->create([
            'idea_id' => $idea->id,
        ]);

        $response = $this->get(route('idea.show', $idea));

        $response->assertSee($commentOne->body);
        $response->assertDontSee(Comment::find(Comment::count())->body);

        $response = $this->get(route('idea.show', [
            'idea' => $idea,
            'page' => 2,
        ]));

        $response->assertDontSee($commentOne->body);
        $response->assertSee(Comment::find(Comment::count())->body);
    }
}
