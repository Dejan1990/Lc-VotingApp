<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    use HasFactory, sluggable;

    const PAGINATION_COUNT = 10;

    protected $guarded = [];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function votes()
    {
        return $this->belongsToMany(User::class, 'votes');
    }

    public function isvotedByUser(?User $user)//?User-> make it optional -> u ovom slucaju user mozda nije ulogovan
    {
        if (!$user) {// ovo radimo zbog ?User $user
            return false;
        }

        return Vote::where('user_id', auth()->id())
            ->where('idea_id', $this->id)
            ->exists();

        //return $this->votes()->where('user_id', $user->id)->exists();
    }
}
