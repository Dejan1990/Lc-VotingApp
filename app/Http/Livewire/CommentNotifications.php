<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use App\Models\Idea;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class CommentNotifications extends Component
{
    const NOTIFICATION_THRESHOLD = 20;
    public $notifications;
    public $notificationCount;
    public $isLoading;

    protected $listeners = ['getNotifications'];

    public function mount()
    {
        $this->notifications = collect([]);
        $this->isLoading = true;
        $this->getNotificationCount();
    }

    public function getNotificationCount()
    {
        $this->notificationCount = auth()->user()->unreadNotifications()->count();

        if ($this->notificationCount > self::NOTIFICATION_THRESHOLD) {
            $this->notificationCount = self::NOTIFICATION_THRESHOLD.'+';
        }
    }

    public function getNotifications()
    {
        $this->notifications = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->take(self::NOTIFICATION_THRESHOLD)
            ->get();

        $this->isLoading = false;
    }

    public function markAsRead($notificationId)
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $notification = DatabaseNotification::findOrFail($notificationId);
        $notification->markAsRead();

        $this->scrollToComment($notification);
    }

    public function scrollToComment($notification)
    {
        $idea = Idea::find($notification->data['idea_id']);
        if (! $idea) {
            return redirect()->route('idea.index')
                ->with('error_message', 'This idea no longer exists!');
        }

        $comment = Comment::find($notification->data['comment_id']);
        if (! $comment) {
            return redirect()->route('idea.index')
                ->with('error_message', 'This comment no longer exists!');
            /*return redirect()->route('idea.show', $notification->data['idea_slug']);
            ovo treba srediti i u testu*/
        }

        $comments = $idea->comments()->pluck('id');
        $indexOfComment = $comments->search($comment->id);

        $page = (int) ($indexOfComment / $comment->getPerPage()) + 1; //da nadjemo komentar kad nije na prvoj strani komentara(pagination)

        session()->flash('scrollToComment', $comment->id);

        return redirect()->route('idea.show', [
            'idea' => $notification->data['idea_slug'],
            'page' => $page
        ]);
    }

    public function markAllAsRead()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        auth()->user()->unreadNotifications->markAsRead();
        $this->getNotificationCount();
        $this->getNotifications();
    }

    public function render()
    {
        return view('livewire.comment-notifications');
    }
}
