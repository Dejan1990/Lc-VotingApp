<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class StatusFilters extends Component
{
    public $status = '';
    //public $status = ''; This way it won't show ?status= when status is empty

    protected $queryString = [
        'status' => ['except' => '']
    ];

    /*protected $queryString = [ This way it won't show ?status= when status is empty
        'status' => ['except' => ''], 
        https://laravel-livewire.com/docs/2.x/query-string ("Keeping A Clean Query String" section)
    ];*/

    public function mount()
    {
        if (Route::currentRouteName() === 'idea.show') {
            $this->status = null;
            $this->queryString = [];
        }
    }

    public function setStatus($newStatus)
    {
        $this->status = $newStatus;
        // dd(Route::currentRouteName());

        //if ($this->getPreviousRouteName() === 'idea.show') {
        return redirect()->route('idea.index', [
            'status' => $this->status
        ]);
        //}
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