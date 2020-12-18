<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    public function Originator()
    {
        return $this->belongsTo('App\Models\User', 'originator_user_id');
    }
}
