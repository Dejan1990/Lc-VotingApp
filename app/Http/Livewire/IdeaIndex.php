<?php

namespace App\Http\Livewire;

use App\Models\Idea;
use Livewire\Component;
use App\Http\Livewire\Traits\WithAuthRedirects;

class IdeaIndex extends Component
{
    use WithAuthRedirects;

    public $idea;
    public $votesCount;
    public $hasVoted;

    public function mount(Idea $idea, $votesCount)
    {
        $this->idea = $idea;
        $this->votesCount = $votesCount;
        $this->hasVoted = $idea->voted_by_user;
    }

    public function vote()
    {
        if (auth()->guest()) {
            return $this->redirectToLogin();
        }

        if ($this->hasVoted) {
            $this->idea->removeVote(auth()->user());
            $this->votesCount--;
            $this->hasVoted = false;
        } else {
            $this->idea->vote(auth()->user());
            $this->votesCount++;
            $this->hasVoted = true;
        }



        /*if (auth()->guest()) { da koristimo toggle() ovako bi radili
            return redirect(route('login'));
        }
        $this->idea->toggle(auth()->user());
        if ($this->hasVoted) {
            $this->hasVoted = false;
            $this->votesCount--;
        } else {
            $this->hasVoted = true;
            $this->votesCount++;
        }*/
    }

    public function render()
    {
        return view('livewire.idea-index');
    }
}
