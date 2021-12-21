<?php

namespace Tests\Feature\Comments;

use App\Http\Livewire\AddComment;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use App\Notifications\CommentAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AddCommentTest extends TestCase
{
    use RefreshDatabase;

     /*You don't need to create users alongside the idea factory, because you can use the existing relationship with the idea model ($idea->user), as its factory creates a user for you.*/

    /** @test */
    public function add_comment_livewire_component_render()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('add-comment');
    }

    /** @test */
    public function add_comment_form_renders_when_user_is_logged_in()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSee('Share your thoughts');
    }

    /** @test */
    public function add_comment_form_does_not_render_when_user_is_logged_out()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSee('Share your thoughts');
    }

    /** @test */
    public function add_comment_form_validation_works()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        Livewire::actingAs($user)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', '')
            ->call('addComment')
            ->assertHasErrors('comment')
            ->set('comment', 'ab')
            ->assertHasErrors('comment');
    }

    /** @test */
    public function add_comment_form__works()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        Notification::fake();
        Notification::assertNothingSent();

        Livewire::actingAs($user)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'My first comment')
            ->call('addComment')
            ->assertEmitted('commentWasAdded');

        Notification::assertSentTo(
            [$idea->user],
            CommentAdded::class
        );

        $this->assertEquals(1, Comment::count());
        $this->assertEquals('My first comment', $idea->comments->first()->body);
    }  
}
