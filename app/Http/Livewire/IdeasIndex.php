<?php

namespace App\Http\Livewire;

use App\Models\Idea;
use App\Models\Vote;
use App\Models\Status;
use Livewire\Component;
use Livewire\WithPagination;

class IdeasIndex extends Component
{
    //use WithPagination;

    public function render()
    {
        $statuses = Status::all()->pluck('id', 'name');// ovo smo postavili da iskoristimo u when

        return view('livewire.ideas-index', [
            'ideas' => Idea::with('user', 'category', 'status')
                ->when(request()->status && request()->status !== '', function ($query) use ($statuses) {
                    return $query->where('status_id', $statuses->get(request()->status));
                })
                ->addSelect(['voted_by_user' => Vote::select('id')
                    ->where('user_id', auth()->id())
                    ->whereColumn('idea_id', 'ideas.id')
                ])
                ->withCount('votes')
                ->orderBy('id', 'desc')
                ->simplePaginate(Idea::PAGINATION_COUNT),
        ]);
    }
}
