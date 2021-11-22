<?php

namespace App\Http\Livewire;

use App\Models\Status;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class StatusFilters extends Component
{
    public $status;
    //public $status = ''; This way it won't show ?status= when status is empty
    public $statusCount;

    /*protected $queryString = [ This way it won't show ?status= when status is empty
        'status' => ['except' => ''], 
        https://laravel-livewire.com/docs/2.x/query-string ("Keeping A Clean Query String" section)
    ];*/

    public function mount() 
    {
        $this->statusCount = Status::getCount();
        $this->status = request()->status ?? '';

        if (Route::currentRouteName() === 'idea.show') { // nije neophodno
            $this->status = null;
        }
    }

    public function setStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->emit('queryStringUpdatedStatus', $this->status);

        if ($this->getPreviousRouteName() === 'idea.show') {
            return redirect()->route('idea.index', [
                'status' => $this->status
            ]);
        }
    }

    public function render()
    {
        return view('livewire.status-filters');
    }

    public function getPreviousRouteName()
    {
        return app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
    }
}
