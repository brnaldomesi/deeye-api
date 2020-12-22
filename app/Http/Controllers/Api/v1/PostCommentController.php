<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\Comment;
use App\Models\Post;

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
        
        return response(json_encode($comment), 201)->header('Content-Type', 'text/json');
    }

    public function update(Request $request)
    {

    }
}
