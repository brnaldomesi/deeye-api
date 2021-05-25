<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Models\Profile;
use App\Models\Follow;

class Post extends Model
{
    protected $appends = [
        'likes_count', 'liked', 'reported', 'saved', 'shares_count', 'shared', 'saved',
        'comments_count', 'recent_commentors',
        'recent_comments',
        'author', 'missing_post_content', 'post_attachments', 'post_source', 'follow_state', 'avatar_path', 'full_name'
    ];

    protected $visible = [
        'id', 'profile_id', 'post_type', 'description', 'link', 'parent_id', 'created_at', 'updated_at',
        'author', 'likes_count', 'liked', 'saved', 'reported', 'shares_count', 'shared', 'saved',
        'comments_count', 'recent_commentors', 'recent_comments', 'missing_post_content', 'post_attachments', 'post_source', 'follow_state', 'avatar_path', 'full_name'
    ];

    protected $guarded = [];

    public function writer()
    {
        return $this->belongsTo('App\Models\Profile', 'profile_id');
    }

    public function activity()
    {
        return $this->belongsTo('App\Models\Activity');
    }

    public function missingPost()
    {
        return $this->hasOne('App\Models\MissingPost');
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

    public function getAuthorAttribute()
    {
        return $this->writer;
    }

    public function getMissingPostContentAttribute()
    {
        return $this->missingPost;
    }

    public function getPostAttachmentsAttribute()
    {
        return $this->attachments;
    }

    public function getPostSourceAttribute()
    {
        return $this->source;
    }

    public function getFollowStateAttribute()
    {
        if(Auth::user()){
            $profile_id = $this->profile_id;
            $posted_user = Profile::where('id', '=', $profile_id)->get('user_id');
            $posted_user_id = $posted_user[0]->user_id;
            $user_id = Auth::user()->id;
            $follow = Follow::where('followes_id', '=', $user_id)->where('follower_id', '=', $posted_user_id)->get();
            if(count($follow) == 0) return 0;
            else return 1;
        }
    }

    public function getAvatarPathAttribute()
    {
      if(Auth::user()){
          $user = Auth::user();
          $profile = Profile::where('user_id', $user->id)->first();
          return $profile->avatar_path;
      }
    }

    public function getFullNameAttribute()
    {
      if(Auth::user()){
          $user = Auth::user();
          $profile = Profile::where('user_id', $user->id)->first();
          return $profile->first_name . ' ' . $profile->last_name;
      }
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

    public function getReportedAttribute()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $profile = Profile::where('user_id', $user->id)->first();
        return $this->activity->actions()->where([['profile_id', $profile->id], ['action_type', 'report']])->count() > 0;
    }

    public function getSharesCountAttribute()
    {
        return Post::where('post_type', 'Share')->where('parent_id', $this->id)->count();
    }

    public function getSharedAttribute()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $profile = Profile::where('user_id', $user->id)->first();
        return Post::where('post_type', 'Share')->where('parent_id', $this->id)->where('profile_id', $profile->id)->count() > 0;
    }
    
    public function getSavedAttribute()
    {
      $user = Auth::user();
      if (!$user) {
          return false;
      }
      $profile = Profile::where('user_id', Auth::user()->id)->first();
      return $this->activity->actions()->where([['profile_id', $profile->id], ['action_type', 'save']])->count() > 0;
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getRecentCommentorsAttribute()
    {
      $limit = config('app.pagination_limit');
      $ids = $this->comments()->groupBy('profile_id')->pluck('profile_id');
      return Profile::whereIn('id', $ids)->orderBy('updated_at', 'desc')->limit($limit)->get();
    }

    public function getRecentCommentsAttribute()
    {
        $limit = config('app.pagination_limit');
        return $this->comments()->whereNull('parent_id')->orderBy('updated_at', 'desc')->limit($limit)->get();
    }

    public function scopeActive($query, $id)
    {
        return $query->where('activity_id', $id);
    }

    public function scopeType($query)
    {
        return $query->where('type', 'Post');
    }
}