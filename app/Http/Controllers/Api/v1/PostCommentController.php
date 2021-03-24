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
use App\Models\Profile;
use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Action;

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
        $target_profileId = $post->profile_id;
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
        
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        $profileId = $profile->id;

        $action = new Action;
        $action->activity_id = $activity_id;
        $action->profile_id = $profileId;
        $action->target_profile_id = $target_profileId;
        $action->type = 'Post';
        // $action->action = ...;
        $action->action_type = 'comment';
        
        if($action->profile_id !== $target_profileId){
            $action->save();
            $postOwner = $post->writer->user;
            $notifyArr = [
                'avatar_path' => $profile->avatar_path,
                'name' => $profile->first_name . ' ' . $profile->last_name
            ];
            if ($post->post_type === 'MissingPerson') {
                $postOwner->notify(new MissingPostCommented($notifyArr));
            }else{
                $postOwner->notify(new PostCommented($notifyArr));
            }
        }

        return response(json_encode($post), 201)->header('Content-Type', 'text/json');
    }

    public function update(Request $request)
    {

    }
}
