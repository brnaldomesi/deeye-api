<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Action extends Model
{
    // protected $visible = [
       
    // ];

    public function missingPost()
    {
      return $this->hasOneThrough(
        'App\Models\MissingPost',
        'App\Models\Post',
        activity_id,
        post_id,
        activity_id,
        id
      );
    }

    public function profile()
    {
        return $this->belongsTo('App\Models\Profile', 'profile_id');
    }

    public function activity()
    {
        return $this->belongsTo('App\Models\Activity', 'activity_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'activity_id', 'activity_id');
    }

    public function comment()
    {
        return $this->belongsTo('App\Models\Comment', 'activity_id', 'activity_id');
    }

    public function scopeOfUser($query, $id)
    {
        return $query->orWhere('target_profile_id', $id);
    }

    public function scopeOfList($query, $id)
    {
        return $query->orWhere('profile_id', '<>', $id)
                ->where('target_profile_id', '0');
    }

    public function scopeOfVerify($query)
    {
        return $query->where('verified', 0);
    }

    public function scopeOfAlert($query, $id)
    {
        return $query->orWhere('target_profile_id', 'like', '['.$id.',%')
          ->orWhere('target_profile_id', 'like', '%,'.$id.',%')
          ->orWhere('target_profile_id', 'like', '%,'.$id.']')
          ->orWhere('target_profile_id', 'like', '['.$id.']');
    }
}
