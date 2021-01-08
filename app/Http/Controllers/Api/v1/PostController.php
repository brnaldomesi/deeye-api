<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\Post;

use App\Repositories\PostRepository;

class PostController extends Controller
{
    protected $postRepository;

    function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {
        return response()->json(Post::where('visible', 1)->get());
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $p_rules = [
            'post_type' => [
                'required',
                Rule::in(['Image','MissingPerson','Share','Video'])
            ],
            'attachments' => [
                'required',
                'array'
            ]
        ];
        $p_validator = Validator::make($inputs, $p_rules);
        if ($p_validator->fails()) {
            return response($p_validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
        }

        $a_rules = [
            'id' => [
                'required',
                'integer'
            ],
            'attachment_type' => [
                'required',
                Rule::in(['General','Company','Verification'])
            ]
        ];
        foreach ($request->attachments as $attach) {
            $a_validator = Validator::make($attach, $a_rules);
            if ($a_validator->fails()) {
                return response($a_validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
            }
        }

        if ($request->post_type == 'MissingPerson')
        {
            $m_rules = [
                'missing_post.missing_type' => [
                    'required',
                    Rule::in(['Medical_Fragile_Missing','Family_Abduction','Endanger_Run_Away','Run_Away','Missing_person'])
                ],
                'missing_post.badge_awarded' => [
                    'required',
                    Rule::in(['Awarded','Pending'])
                ],
                'missing_post.sex' => [
                    'required',
                    Rule::in(['Female','Male'])
                ],
                'missing_post.hair' => [
                    'required',
                    Rule::in(['Yellow','Wave','Blond','White','Black'])
                ],
                'missing_post.race' => [
                    'required',
                    Rule::in(['Black','White','Yellow'])
                ],
                'missing_post.eye' => [
                    'required',
                    Rule::in(['Yellow','Brown','Blue','Black'])
                ],
            ];
            $m_validator = Validator::make($inputs, $m_rules);
            if ($m_validator->fails()) {
                return response($m_validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
            }
        }
        $post = $this->postRepository->createPost($inputs);
        $this->postRepository->linkAttachmentsWithPost($request->attachments, $post);
        return response(json_encode($post), 201)->header('Content-Type', 'text/json');
    }

    public function show(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post)
        {
            return response(json_encode([
                'message' => 'No such post'
            ]), 404)->header('Content-Type', 'text/json');
        } else {
            return response(json_encode($post), 200)->header('Content-Type', 'text/json');
        }
    }

    public function update(Request $request, $id)
    {
      $post = Post::find($id);
      $post->update($request->all());
      return response(json_encode($post), 200)->header('Content-Type', 'text/json');
    }
}
