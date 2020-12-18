<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'activity_id');
    }
}
