<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostAttachment extends Model
{
    protected $appends = [
        'path', 'file_type'
    ];

    protected $visible = [
        'id', 'post_id', 'attachment_type', 'path', 'file_type',
    ];

    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

    public function attachment()
    {
        return $this->hasOne('App\Models\Attachment', 'attachment_id');
    }

    public function getPathAttribute()
    {
        return $this->attachment->path;
    }

    public function getFileTypeAttribute()
    {
        return $this->attachment->file_type;
    }
}
