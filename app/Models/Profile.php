<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

    protected $appends = [ 'email' ];

    protected $hidden = [ 'user' ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function posts() {
      return $this->hasMany('App\Models\Post', 'profile_id');
    }

    public function FollowerUserList() 
    {
        return $this->belongsTo('App\Models\Follow', 'user_id', 'follower_id');
    }
    
    public function FollowesUserList() 
    {
        return $this->belongsTo('App\Models\Profile', 'user_id', 'follwes_id');
    }

    public function Follows()
    {
        return $this->hasMany('App\Models\Follow', 'user_id', 'follower_id');
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }
}
