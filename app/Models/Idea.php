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

    public function isVotedByUser(?User $user) //?User-> make it optional -> u ovom slucaju user mozda nije ulogovan
    {
        if (!$user) { // ovo radimo zbog ?User $user
            return false;
        }

        return Vote::where('user_id', $user->id)
            ->where('idea_id', $this->id)
            ->exists();

        //return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function vote(User $user)
    {
        Vote::create([
            'idea_id' => $this->id,
            'user_id' => $user->id
        ]);
    }

    public function removeVote(User $user)
    {
        Vote::where('idea_id', $this->id)
            ->where('user_id', $user->id)
            ->first()
            ->delete();
    }

   /* public function vote(User $user)
    {
        $this->votes()->attach($user);
    }

    public function removeVote(User $user)
    {
        $this->votes()->detach($user);
    }*/

    /*public function toggle(User $user)
    {
        $this->isVotedByUser($user) ?
            $this->votes()->detach($user) : $this->votes()->attach($user);
    }*/
}
