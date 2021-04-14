<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\MissingPost;
use App\Models\Activity;

class PostRepository
{
    public function createPost($values, $id)
    {
        $activity = new Activity;
        $activity->save();
        
        $params = ['post_type', 'description', 'link', 'parent_id'];
        if($id == 0) {
            $post = new Post;
        } else {
            $post = Post::find($id);
        }
        foreach ($params as $key) {
            if (isset($values[$key])) {
                $post->$key = $values[$key];
            }
        }
        $post->activity_id = $activity->id;
        $user = User::find(Auth::user()->id);
        $post->profile_id = $user->profile->id;
        $post->save();
        
        if ($values['post_type'] == 'MissingPerson') {
            if($id == 0)
                $this->createMissingPost($values['missing_post'], $post, $id);
            // else
            //     $this->createMissingPost($values['missing_post'], $post, $post->id);
        }
        return $post;
    }

    public function createMissingPost($values, $post, $id)
    {
        if($id == 0)
            $mpost = new MissingPost;
        else {
            $m_id = MissingPost::where('post_id', $id)->get('id');
            $mpost = MissingPost::find($m_id[0]->id);
        }
        $params = ['missing_type', 'language', 'badge_awarded', 'duo_location', 'is_draft', 'has_tattoo', 'fullname', 'aka', 'dob', 
            'height_ft', 'height_cm', 'weight_kg', 'weight_lb', 'sex', 'hair', 'race',
            'eye', 'medical_condition', 'missing_since','missing_location_zip', 'missing_location_street', 'missing_location_city', 'missing_location_country', 'missing_location_state', 
            'circumstance', 'contact_email', 'contact_phone_number1', 'contact_phone_number2', 'verification_groupchat_link', 'company_name', 'missing_location_latitude', 'missing_location_longitude',
        ];
        $mpost->post_id = $post->id;
        foreach ($params as $key) {
            if (isset($values[$key])) {
                $mpost->$key = $values[$key];
            }
        }
        $mpost->save();
    }

    public function linkAttachmentsWithPost($attachments, $post)
    {
        $post_attachments = $post->post_attachments;
        foreach ($attachments as $attach) {
            $cnt = 0;
            foreach($post_attachments as $att){
                if($attach['id'] == $att['attachment_id'])
                    $cnt ++;
            }
            if($cnt == 0) {
                $pa = new PostAttachment;
                $pa->post_id = $post->id;
                $pa->attachment_id = $attach['id'];
                $pa->attachment_type = $attach['attachment_type'];
                $pa->save();
            }
        }
    }
}