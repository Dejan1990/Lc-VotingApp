<?php

namespace Tests\Feature;

use App\Http\Livewire\AddComment;
use App\Http\Livewire\CommentNotifications;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;
use Tests\TestCase;

class CommentNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function comment_notifications_livewire_component_renders_when_user_logged_in()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertSeeLivewire('comment-notifications');
    }

    /** @test */
    public function comment_notifications_livewire_component_does_not_render_when_user_not_logged_in()
    {
        $this->get('/')
            ->assertDontSeeLivewire('comment-notifications');
    }

    /** @test */
    public function notifications_show_for_logged_in_user()
    {
        $user = User::factory()->create();

        $idea = Idea::factory()->create([
            'user_id' => $user->id
        ]);

        $userCommenting = User::factory()->create();

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'First comment')
            ->call('addComment');

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'Second comment')
            ->call('addComment');

        DatabaseNotification::first()->update(['created_at' => now()->subMinute()]);

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->assertSeeInOrder(['Second comment', 'First comment'])
            ->assertSet('notificationCount', 2);
    }

    /** @test */
    public function notification_count_greater_than_threshold_shows_for_logged_in_user()
    {
        $user = User::factory()->create();
        $idea  = Idea::factory()->create([
            'user_id' => $user->id
        ]);

        $userCommenting = User::factory()->create();
        $treshold = CommentNotifications::NOTIFICATION_THRESHOLD;

        foreach (range(1, $treshold + 1) as $item) {
            Livewire::actingAs($userCommenting)
                ->test(AddComment::class, [
                    'idea' => $idea
                ])
                ->set('comment', 'Commenting...')
                ->call('addComment');
        }

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->assertSet('notificationCount', $treshold.'+')
            ->assertSee($treshold.'+');
    }

    /** @test */
    public function can_mark_all_notifications_as_read() 
    {
        $user = User::factory()->create();
        $idea  = Idea::factory()->create([
            'user_id' => $user->id
        ]);

        $userCommenting = User::factory()->create();

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'First comment')
            ->call('addComment');

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'Second comment')
            ->call('addComment');

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAllAsRead');

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    /** @test */
    public function can_mark_individual_notification_as_read()
    {
        $user = User::factory()->create();
        $idea  = Idea::factory()->create([
            'user_id' => $user->id
        ]);

        $userCommenting = User::factory()->create();

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'First comment')
            ->call('addComment');

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'Second comment')
            ->call('addComment');

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect(route('idea.show', [
                'idea' => $idea,
                'page' => 1
            ]));

        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());
    }

    /** @test */
    public function notification_idea_deleted_redirects_to_index_page()
    {
        $user = User::factory()->create();
        $idea  = Idea::factory()->create([
            'user_id' => $user->id
        ]);

        $userCommenting = User::factory()->create();

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'First comment')
            ->call('addComment');

        $idea->comments()->delete();
        $idea->delete();

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect('/');
    }

    /** @test */
    public function notification_comment_deleted_redirects_to_index_page()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userCommenting = User::factory()->create();

        Livewire::actingAs($userCommenting)
            ->test(AddComment::class, [
                'idea' => $idea
            ])
            ->set('comment', 'First comment')
            ->call('addComment');

        $idea->comments()->delete();

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect('/');
    }
}
