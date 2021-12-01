<?php

namespace App\Http\Livewire;

use App\Jobs\NotifyAllVoters;
use App\Mail\IdeasStatusUpdatedMailable;
use App\Models\Idea;
use Livewire\Component;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class SetStatus extends Component
{
    public Idea $idea;
    public $status;
    public $notifyAllVoters;

    /*public function mount(Idea $idea)
    {
        $this->idea = $idea;
        $this->status = $this->idea->status_id;
    }*/

    public function mount()
    {
        $this->status = $this->idea->status_id;
    }

    public function setStatus()
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->idea->status_id = $this->status;
        $this->idea->save();

        if ($this->notifyAllVoters) {
            NotifyAllVoters::dispatch($this->idea);
        }

        $this->emit('statusWasUpdated', 'Status was updated successfully!');
    }

    public function render()
    {
        return view('livewire.set-status');
    }
}
