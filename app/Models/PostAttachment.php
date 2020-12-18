<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostAttachment extends Model
{
    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

    public function attachment()
    {
        return $this->hasOne('App\Models\Attachment', 'attachment_id');
    }
}
