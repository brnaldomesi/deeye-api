<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public function actions()
    {
        return $this->hasMany('App\Models\Action', 'activity_id');
    }

    public function actionPost()
    {
        return $this->hasOneThrough('App\Models\Post', 'App\Models\Action');
    }

    public function posts()
    {
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

    public function comments()
    {
        return $this->belongsTo('App\Models\Comment', 'comment_id');
    }
}
