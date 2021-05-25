<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\PostCommented;
use App\Notifications\MissingPostCommented;
use Illuminate\Support\Facades\Notification;
use App\Models\Profile;
use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Action;
use App\Models\Follow;

use App\Repositories\CommentRepository;

class PostCommentController extends Controller
{
    protected $commentRepository;

    function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function index(Request $request, $postId)
    {
        $comments = $this->commentRepository->commentsForPost($postId);
        return response()->json($comments);
    }

    public function store(Request $request, $postId)
    {
        $post = Post::find($postId);
        $activity_id = $post->activity_id;
        $userInfo = Auth::user();
        $profile = Profile::where('user_id', $userInfo->id)->first();
        $profileId = $profile->id;
        $users = Action::ofActivity($activity_id)->ofType()->ofActiontype()->get('profile_id');
        $followUsers = Follow::ofFollowes($userInfo->id)->get('followes_id');
        $target_profileId = $post->profile_id;
        $temp = Array();
        $tmp = Array();
        $cnt = 0;
        foreach($users as $user) {
          if(!in_array($user->profile_id, $temp) && $user->profile_id !== $target_profileId && $user->profile_id !== $profileId){
            $temp[$cnt] = $user->profile_id;
            $tmp[$cnt] = Profile::where('id', $user->profile_id)->get('user_id');
            $cnt ++;
          }
        }
        foreach($followUsers as $user) {
          if(!in_array($user->followes_id, $temp) && $user->followes_id !== $target_profileId){
            $temp[$cnt] = $user->followes_id;
            $tmp[$cnt] = Profile::where('id', $user->followes_id)->get('user_id');
            $cnt ++;
          }
        }
        $inputs = $request->all();
        $rules = [
          'text' => [
          'required'
            ]
        ];
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
          return response($validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
        }
        $comment = $this->commentRepository->createCommentForPost($postId, $request->text);
            
        $action = new Action;
        $action->activity_id = $activity_id;
        $action->profile_id = $profileId;
        $action->type = 'Post';
        $action->action_type = 'comment';
        if(!in_array($target_profileId, $temp) && $action->profile_id !== $target_profileId && $user->profile_id !== $profileId){
            $temp[$cnt] = $target_profileId;
            $tmp[$cnt] = Profile::where('id', $target_profileId)->get('user_id');
        }
        $notification_users = User::notification($tmp)->get();
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->save();
        $postOwner = $post->writer->user;
        $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
        ];
        if ($post->post_type === 'MissingPerson') {
            Notification::send($notification_users, new MissingPostCommented($notifyArr));
            // $postOwner->notify(new MissingPostCommented($notifyArr));
        }else{
            Notification::send($notification_users, new PostCommented($notifyArr));
            // $postOwner->notify(new PostCommented($notifyArr));
        }
        return response(json_encode($post), 201)->header('Content-Type', 'text/json');
    }

    public function update(Request $request)
    {

    }
}
