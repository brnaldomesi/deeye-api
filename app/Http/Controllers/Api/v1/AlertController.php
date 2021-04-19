<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // $profile = Auth::user()->profile;
        // return Action::ofAlert($profile->id)
        //   ->ofVerify()
        //   ->with([
        //     'activity', 
        //     'post', 
        //     'comment', 
        //     'profile'
        //   ])->orderBy('updated_at', 'desc')->get();
      
        $profile_id = Auth::user()->profile->id;
        $actions = DB::table('actions')
            ->leftJoin('profiles', 'profiles.id', '=', 'actions.profile_id')
            ->leftJoin('posts', 'posts.activity_id', '=', 'actions.activity_id')
            ->leftJoin('missing_posts', 'missing_posts.post_id', '=', 'posts.id')
            ->orWhere('target_profile_id', 'like', '['.$profile_id.',%')
            ->orWhere('target_profile_id', 'like', '%,'.$profile_id.',%')
            ->orWhere('target_profile_id', 'like', '%,'.$profile_id.']')
            ->orWhere('target_profile_id', 'like', '['.$profile_id.']')
            ->where('verified', 0)
            ->orderBy('actions.updated_at', 'desc')
            ->select('actions.id', 'actions.type', 'action_type', 'actions.updated_at', 'duo_location', 'missing_since', 'first_name', 'last_name', 'avatar_path')
            ->get();
            
        return response()->json($actions);
    }
}