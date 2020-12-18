<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $appends = [
        'author', 'likes', 'liked', 'shares', 'shared',
        'comments'
    ];

    public function author()
    {
        return $this->belongsTo('App\Models\Profile', 'profile_id');
    }

    public function activity()
    {
        return $this->hasOne('App\Models\Activity', 'activity_id');
    }

    public function missingPost()
    {
        return $this->hasOne('App\Models\MissingPost', 'post_id');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\PostAttachment');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\Post', 'parent_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comments', 'post_id');
    }
}
