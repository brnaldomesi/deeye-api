<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Notifications\CommentCommented;
use App\Notifications\CommentSupported;
use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\CommentRepository;
use App\Models\Activity;
use App\Models\Action;
use App\Models\Profile;
class CommentController extends Controller
{
    protected $commentRepository;

    function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function index(Request $request, $commentId)
    {
        $comments = $this->commentRepository->commentsForComment($commentId);
        return response()->json($comments);
    }

    public function store(Request $request, $commentId)
    {
        $comment_info = Comment::find($commentId);
        $activity_id = $comment_info->activity_id;
        $target_profileId = $comment_info->profile_id;
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
        
        $comment = $this->commentRepository->createCommentForComment($commentId, $request->text);
        
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        $profileId = $profile->id;

        $action = new Action;
        $action->activity_id = $activity_id;
        $action->profile_id = $profileId;
        $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->type = 'Comment';
        // $action->action = ...;
        $action->action_type = 'comment';

        if($action->profile_id !== $target_profileId){
          $action->save();
          $commentOwner = $comment_info->writer->user;
          $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
          ];
          $commentOwner->notify(new CommentCommented($notifyArr));
        }

        return response(json_encode($comment), 201)->header('Content-Type', 'text/json');
    }

    public function update(Request $request)
    {

    }

    public function like(Request $request, $id)
    {
      $comment = Comment::find($id);
      $target_profileId = $comment->profile_id;
      $liked = $comment->liked;
      $user = Auth::user();
      $profile = Profile::where('user_id', $user->id)->first();

      $profileId = $profile->id;
      $activityId = $comment->activity_id;
      $activity = Activity::find($activityId);
      if($liked) {
        $activity->likes -= 1;
      } else {
        $activity->likes += 1;
      }
      $activity->save();

      if($liked) {
        Action::where([['profile_id', $profile->id], ['action_type', 'like'], ['activity_id', $activityId]])->delete();
      } else {
        $action = new Action;
        $action->activity_id = $activityId;
        $action->profile_id = $profileId;
        $action->type = 'Comment';
        $action->action_type = 'like';
        $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->save();
        
        if($action->profile_id !== $target_profileId){
          $commentOwner = $comment->writer->user;
          $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
          ];
          $commentOwner->notify(new CommentSupported($notifyArr));
        }
      }

      return response()->json($comment);
    }
}
