<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public function actions()
    {
        return $this->hasMany('App\Models\Action', 'activity_id');
    }
}
