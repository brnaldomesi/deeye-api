<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingPost extends Model
{
    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

    public function activity()
    {
        return $this->belongsTo('App\Models\Activity', 'activity_id');
    }

    public function scopeMiss($query) 
    {
        return $query->where('post_id', $id);
    }
}
