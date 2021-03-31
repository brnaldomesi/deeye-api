<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public function scopeGetFollowes($query, $id, $user_id)
    {
        return $query->where('follower_id', '=',  $id)->where('followes_id', '=', $user_id);
    }
    
    public function scopeGetFollower($query, $id, $user_id)
    {
        return $query->where('followes_id', '=', $id)->where('follower_id', '=', $user_id);
    }

    public function follower() 
    {
        return $this->belongsTo('App\Models\Profile', 'follower_id', 'user_id');
    }

    public function followes() 
    {
        return $this->belongsTo('App\Models\Profile', 'followes_id', 'user_id');
    }
}