<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('App\Model\Post', 'bookmarks', 'profile_id', 'post_id');
    }
}
