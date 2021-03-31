<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\Profile;
use App\Models\User;

class FollowController extends Controller
{
    public function index(Request $request) {
        $user_id = Auth::user()->id;
        $search = $request->search;
        $type = $request->type;

        $users = DB::table('follows')
            ->join('profiles', 'profiles.user_id', '=', $type != 0 ? 'follows.follower_id' : 'follows.followes_id')
            ->where('profiles.first_name', 'like', '%'.$search.'%')
            ->where($type == 0 ? 'follows.follower_id' : 'follows.followes_id', '=', $user_id)
            ->select('profiles.user_id as id', 'profiles.first_name', 'profiles.last_name', 'profiles.avatar_path')
            ->get();

        return $users;
    }

    public function follow(Request $request) {
        $follower_id = $request->user_id;
        $followes_id = Auth::user()->id;
        
        if($request->type == 'follow'){
            $temp = Follow::where('follower_id', $follower_id)->where('followes_id', $followes_id)->get();
            if(count($temp) == 0){
                $follow = new Follow;
                $follow->follower_id = $follower_id;
                $follow->followes_id = $followes_id;
                return $follow->save();
            }
        } elseif($request->type == 'unfollow') {
            $follow = Follow::getFollowes($follower_id, $followes_id);
            return $follow->delete();
        } elseif($request->type == 'remove') {
            $follow = Follow::getFollower($follower_id, $followes_id);
            return $follow->delete();
        }
    }
}
