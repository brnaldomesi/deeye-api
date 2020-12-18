<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    protected $appends = [
        'likes_count', 'liked', 'shares_count', 'shared', 'saved',
        'comments_count', 'recent_commentors',
        'recent_comments',
    ];

    protected $visible = [
        'id', 'profile_id', 'post_type', 'description', 'link', 'parent_id', 'created_at', 'updated_at',
        'author', 'likes_count', 'liked', 'shares_count', 'shared', 'saved',
        'comments_count', 'recent_commentors',
        'recent_comments',
        'missing_post', 'attachments', 'source'
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

    public function bookmarkers()
    {
        return $this->belongsToMany('App\Models\Profile', 'post_id', 'profile_id');
    }

    public function getLikesCountAttribute()
    {
        return $this->activity->likes()->count();
    }

    public function getLikedAttribute()
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        return $this->activity->likes()->where('profile_id', $profile->id)->count() > 0;
    }

    public function getSharesCountAttribute()
    {
        return Post::where('post_type', 'Shared')->where('parent_id', $this->id)->count();
    }

    public function getSharedAttribute()
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        return Post::where('post_type', 'Shared')->where('parent_id', $this->id)->where('profile_id', $profile->id)->count() > 0;
    }
    
    public function getSavedAttribute()
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        return $this->bookmarkers()->where('profile_id', $profile->id)->count() > 0;
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getRecentCommentorsAttribute()
    {
        $ids = $this->comments()->groupBy('profile_id')->pluck('profile_id');
        return Profile::whereIn('id', $ids)->get();
    }

    public function getRecentCommentsAttribute()
    {
        return $this->comments()->orderBy('created', 'desc')->limit(5);
    }
}
