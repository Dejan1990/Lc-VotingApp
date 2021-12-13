<?php

namespace App\Http\Livewire;

use App\Models\Idea;

use App\Models\Comment;
use App\Notifications\CommentAdded;
use Livewire\Component;
use Illuminate\Http\Response;

class AddComment extends Component
{
    public Idea $idea;
    public $comment;

    protected $rules = [
        'comment' => 'required|min:4'
    ];

    /*public function mount(Idea $idea)
    {
        $this->idea = $idea;
    }*/

    public function addComment()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $this->validate();

        $newComment = Comment::create([
            'user_id' => auth()->id(),
            'idea_id' => $this->idea->id,
            'status_id' => 1,
            'body' => $this->comment,
        ]);

        $this->reset('comment');

        //Ako necemo da nam stize notifikacija kad komentarisemo sopstvenu ideju, ovde postavljamo uslov
        if ($this->idea->user_id !== auth()->id()) {
            $this->idea->user->notify(new CommentAdded($newComment));
        }

        $this->emit('commentWasAdded', 'Comment was posted!');
    }
    public function render()
    {
        return view('livewire.add-comment');
    }
}
