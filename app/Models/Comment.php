<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    protected $appends = [
        'likes_count', 'liked', 'comments_count'
    ];

    protected $visible = [
        'id', 'profile_id', 'text',
        'author', 'likes_count', 'liked',
        'comments_count',
        'created_at', 'updated_at'
    ];

    public function author()
    {
        return $this->belongsTo('App\Models\Profile', 'profile_id');
    }

    public function activity()
    {
        return $this->belongsTo('App\Models\Activity');
    }

    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Comment', 'parent_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'parent_id');
    }

    public function getLikesCountAttribute()
    {
        return $this->activity->likes;
    }

    public function getLikedAttribute()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $profile = Profile::where('user_id', $user->id)->first();
        return $this->activity->actions()->where([['profile_id', $profile->id], ['action_type', 'like']])->count() > 0;
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments->count();
    }
}
