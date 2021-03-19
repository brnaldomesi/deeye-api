<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Action;
use App\Models\Activity;
use App\Models\Profile;
use App\Models\Post;
use App\Models\MissingPost;

class AlertController extends Controller
{
    public function index() {
        $data = Array();
        $activities = Action::all();
        $cnt = 0;
        foreach($activities as $activity) {
            $data[$cnt]["send_user"] = Profile::find($activity->profile_id);
            $data[$cnt]["action_type"] = $activity->type;
            $data[$cnt]["activity_info"] = Activity::find($activity->activity_id);
            $data[$cnt]["receive_user"] = Post::active($activity->activity_id)->first()->author;
            $postType = Post::active($activity->activity_id)->first()->post_type;
            $postID = Post::active($activity->activity_id)->first()->id;
            if($postType === "MissingPerson"){
                $data[$cnt]["post_data"] = MissingPost::miss($postID)->first();
            }else {
                $data[$cnt]["post_data"] = Post::active($activity->activity_id)->first();
            }
            $cnt ++;
        }
        return $data;
    }
}
