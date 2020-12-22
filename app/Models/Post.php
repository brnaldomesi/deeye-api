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
        'author', 'missing_post_content', 'post_attachments', 'source', 'bookmarkers_count',
    ];

    protected $visible = [
        'id', 'profile_id', 'post_type', 'description', 'link', 'parent_id', 'created_at', 'updated_at',
        'author', 'likes_count', 'liked', 'shares_count', 'shared', 'saved',
        'comments_count', 'recent_commentors', 'bookmarkers_count',
        'recent_comments',
        'missing_post_content', 'post_attachments', 'post_source'
    ];

    public function writer()
    {
        return $this->belongsTo('App\Models\Profile', 'profile_id');
    }

    public function activity()
    {
        return $this->belongsTo('App\Models\Activity', 'activity_id');
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
        return $this->hasMany('App\Models\Comment', 'post_id');
    }

    public function bookmarkers()
    {
        return $this->belongsToMany('App\Models\Profile', 'bookmarks', 'post_id', 'profile_id');
    }

    public function getAuthorAttribute()
    {
        return $this->writer;
    }

    public function getMissingPostContentAttribute()
    {
        return $this->missing_post;
    }

    public function getPostAttachmentsAttribute()
    {
        return $this->attachments;
    }

    public function getPostSourceAttribute()
    {
        return $this->soure;
    }

    public function getBookmarkersCountAttribute()
    {
        return $this->bookmarkers;
    }

    public function getLikesCountAttribute()
    {
        return $this->activity->likes()->count();
    }

    public function getLikedAttribute()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $profile = Profile::where('user_id', $user->id)->first();
        return $this->activity->likes()->where('profile_id', $profile->id)->count() > 0;
    }

    public function getSharesCountAttribute()
    {
        return Post::where('post_type', 'Shared')->where('parent_id', $this->id)->count();
    }

    public function getSharedAttribute()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $profile = Profile::where('user_id', $user->id)->first();
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
        $limit = config('app.pagination_limit');
        return $this->comments()->whereNull('parent_id')->orderBy('created_at', 'desc')->paginate($limit);
    }
}
