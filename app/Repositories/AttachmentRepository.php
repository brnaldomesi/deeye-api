<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Models\Attachment;
use App\Models\User;

class AttachmentRepository
{
    public function saveAttachFromUploaded($file, $fileType)
    {
        $att = new Attachment;
        if ($fileType == 'Url') {
            $att->path = $file;
        } else {

            $filename = $file->getClientOriginalName();
            $hashed = md5(time());
            $extension = File::extension($filename);
            
            $public_path = "uploads/$fileType";
            $save_path = 'public/' . $public_path;
    
            $file->storeAs($save_path, "$hashed.$extension");
    
            $att->path = $public_path . "/$hashed.$extension";
            
        }
        
        $user = User::find(Auth::user()->id);
        $att->profile_id = $user->profile->id;
        $att->file_type = $fileType;
        
        $att->save();

        return $att;
    }
}
