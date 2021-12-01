<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Idea;
use App\Models\Vote;
use App\Models\Status;
use Livewire\Component;
use Livewire\WithPagination;

class IdeasIndex extends Component
{
    use WithPagination;

    public $status;
    public $category;
    public $filter;
    public $search;

    protected $queryString = [
        'status' => ['except' => ''],
        'category' => ['except' => ''],
        'filter' => ['except' => ''],
        'search' => ['except' => '']
    ];

    protected $listeners = ['queryStringUpdatedStatus'];

    public function mount()
    {
        $this->status = request()->status ?? '';
    }

    public function updatingCategory() 
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        if ($this->filter === 'My Ideas') {
            if (! auth()->check()) {
                return redirect()->route('login');
            }
        }
    }

    public function queryStringUpdatedStatus($newStatus)
    {
        $this->resetPage(); 
        $this->status = $newStatus;
    }

    public function render()
    {
        $statuses = Status::all()->pluck('id', 'name');// ovo smo postavili da iskoristimo u when
        $categories = Category::all(); 

        return view('livewire.ideas-index', [
            'ideas' => Idea::with('user', 'category', 'status')
                ->when($this->status && $this->status !== '', function ($query) use ($statuses) {
                    return $query->where('status_id', $statuses->get($this->status));
                })
                ->when($this->category && $this->category !== '', function ($query) use ($categories) {
                    return $query->where('category_id', $categories->pluck('id', 'name')->get($this->category));
                })
                ->when($this->filter && $this->filter === 'Top Voted', function ($query) {
                    return $query->orderByDesc('votes_count'); // 'votes_count' is from below ->withCount('votes')
                })
                ->when($this->filter && $this->filter === 'My Ideas', function ($query) {
                    return $query->where('user_id', auth()->id());
                })
                ->when($this->filter && $this->filter === 'Spam Ideas', function ($query) {
                    return $query->where('spam_reports', '>', 0)->orderByDesc('spam_reports');
                })
                ->when(strlen($this->search) >= 3, function ($query) {
                    return $query->where('title', 'like', '%'.$this->search.'%');
                })
                ->addSelect(['voted_by_user' => Vote::select('id')
                    ->where('user_id', auth()->id())
                    ->whereColumn('idea_id', 'ideas.id')
                ])
                ->withCount('votes')
                ->withCount('comments')
                ->orderBy('id', 'desc')
                ->simplePaginate(Idea::PAGINATION_COUNT),
                'categories' => $categories
        ]);
    }
}
