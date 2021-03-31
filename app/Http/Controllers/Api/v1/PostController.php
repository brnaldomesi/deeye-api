<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Attachment;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Profile;
use App\Models\MissingPost;
use App\Models\PostAttachment;
use App\Models\Action;
use App\Models\Follow;
use App\Notifications\PostHided;
use App\Notifications\MissingPostHided;
use App\Notifications\PostSupported;
use App\Notifications\MissingPostSupported;
use App\Notifications\PostReported;
use App\Notifications\MissingPostReported;
use App\Notifications\PostSaved;
use App\Notifications\MissingPostSaved;
use App\Notifications\PostShared;
use App\Notifications\MissingPostShared;
use App\Notifications\MissingPostCreated;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Notification;

class PostController extends Controller
{
    protected $postRepository;

    function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {
      $user = Auth::user();
      if($user) {
        $activityIds = Action::where([['profile_id', $user->profile->id], ['action_type', 'hide']])->pluck('activity_id')->all();
        return response()->json(Post::whereNotIn('activity_id', $activityIds)->orderBy('updated_at', 'desc')->get());
      } else {
        return response()->json(Post::orderBy('updated_at', 'desc')->get());
      }
    }

    public function share(Request $request, $id) {
      $user = User::find(Auth::user()->id);
      $post = Post::find($id);
      $target_profileId = $post->profile_id;
      $description = $request->description;
      $sourceId = $post->post_source ? $post->post_source->id : $id;

      $activity = Post::find($sourceId)->activity;
      $activity->share += 1;
      $activity->save();

      $action = new Action;
      $action->activity_id = $activity->id;
      $action->profile_id = $user->profile->id;
      $action->type = 'Post';
      $action->action_type = 'share';
      $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
      $action->save();

      $newActivity = new Activity;
      $newActivity->save();
      
      $newPost = new Post;
      $newPost->post_type = "Share";
      $newPost->description = $description;
      $newPost->profile_id = $user->profile->id;
      $newPost->parent_id = $sourceId;
      $newPost->activity_id = $newActivity->id;
      $newPost->save();

      $profile = Profile::where('user_id', $user->id)->first();
      if($action->profile_id !== $target_profileId){
        $postOwner = $post->writer->user;
        $notifyArr = [
          'avatar_path' => $profile->avatar_path,
          'name' => $profile->first_name . ' ' . $profile->last_name
        ];
        if ($post->post_type === 'MissingPerson') {
          $postOwner->notify(new MissingPostShared($notifyArr));
        }else{
          $postOwner->notify(new PostShared($notifyArr));
        }
      }
      return response()->json($newPost); 
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
            $current_user_id = Auth::user()->id;
            $current_user_followers = User::find($current_user_id)->FollowesUserList()->get();
            $follower_list = '';
            $list = '';
            foreach($current_user_followers as $current_user_follower){
              $follower_list .= $current_user_follower->followes_id;
              $follower_list .= ',';
            }
            $list = rtrim($follower_list, ',');
            $missing_data = $request->missing_post;
            $missing_location_latitude =  $missing_data["missing_location_latitude"];
            $missing_location_longitude =  $missing_data["missing_location_longitude"];

            $users = User::Distance($missing_location_latitude, $missing_location_longitude, 50 * 1.60934, $current_user_id, $list)->get();

            // if($list != ''){
            //   $users = DB::select('call getDistance(?, ?, ?, ?, ?)', array($missing_location_latitude,$missing_location_longitude, 50 * 1.60934, $current_user_id, $list));
            // }else {
            //   $users = DB::select('call getDistance(?, ?, ?, ?, ?)', array($missing_location_latitude,$missing_location_longitude, 50 * 1.60934, $current_user_id, "''"));
            // }

            if($users) {
              $temp = array();
              $cnt = 0;
              foreach($users as $user) {
                $pro_id = Profile::where('user_id', $user->id)->get('id');
                $temp[$cnt] = $pro_id[0]->id;
                $cnt ++;
              }
              $user_list = json_encode($temp);
            }
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
                Rule::in(['Black','White','Yellow', 'American', 'Asian', 'African', 'European', 'Oceanian'])
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
            
            $post = $this->postRepository->createPost($inputs);
            $target_profileId = $post->profile_id;
            $liked = $post->liked;
            $user = Auth::user();
            $profile = Profile::where('user_id', $user->id)->first();
            
            $profileId = $profile->id;
            $activityId = $post->activity_id;
            if($users) {
              $action = new Action;
              $action->activity_id = $activityId;
              $action->profile_id = $profileId;
              $action->type = 'Post';
              $action->action_type = 'create_missing';
              $action->target_profile_id = $user_list;
              $action->save();
            }
            
            $this->postRepository->linkAttachmentsWithPost($request->attachments, $post);
            
            $notifyArr = [
              'avatar_path' => $profile->avatar_path,
              'name' => $profile->first_name . ' ' . $profile->last_name,
            ];
          Notification::send($users, new MissingPostCreated($notifyArr));        
          return response(json_encode($post), 201)->header('Content-Type', 'text/json');
        }else{
          $post = $this->postRepository->createPost($inputs);
          $this->postRepository->linkAttachmentsWithPost($request->attachments, $post);
          return response(json_encode($post), 201)->header('Content-Type', 'text/json');
        }
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
      if ($post->post_type === 'MissingPerson') {
          $missing_post = MissingPost::where('post_id', $id)->get()[0];
          $missing_post->fullname = $request->fullname;
          $missing_post->circumstance = $request->circumstance;
          $missing_post->contact_phone_number1 = $request->contact_phone_number1;
          $missing_post->contact_phone_number2 = $request->contact_phone_number1;
          $missing_post->save();

        return response()->json($post);
      } else {
        $post->update($request->all());
        return response()->json($post);
      }
    }

    public function report(Request $request, $id)
    {
      $post = Post::find($id);
      $target_profileId = $post->profile_id;
      $reported = $post->reported;
      if(!$reported) {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        $profileId = $profile->id;
        $activityId = $post->activity_id;
        $activity = Activity::find($activityId);
        $activity->reported += 1;
        $activity->save();

        $action = new Action;
        $action->activity_id = $activityId;
        $action->profile_id = $profileId;
        $action->type = 'Post';
        $action->action_type = 'report';
        $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->save();

        if($action->profile_id !== $target_profileId){ 
          $postOwner = $post->writer->user;
          $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
          ];
          if ($post->post_type === 'MissingPerson') {
            $postOwner->notify(new MissingPostReported($notifyArr));
          }else{
            $postOwner->notify(new PostReported($notifyArr));
          }
        }
      }

      return response()->json($post);
    }

    public function save(Request $request, $id)
    {
      $post = Post::find($id);
      $target_profileId = $post->profile_id;
      $saved = $post->saved;
      $user = Auth::user();
      $profile = Profile::where('user_id', $user->id)->first();

      $profileId = $profile->id;
      $activityId = $post->activity_id;
      $activity = Activity::find($activityId);
      if($saved) {
        $activity->saved -= 1;
      } else {
        $activity->saved += 1;
      }
      $activity->save();

      if($saved) {
        Action::where([['profile_id', $profile->id], ['action_type', 'save'], ['activity_id', $activityId]])->delete();
      } else {
        $action = new Action;
        $action->activity_id = $activityId;
        $action->profile_id = $profileId;
        $action->type = 'Post';
        $action->action_type = 'save';
        $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->save();

        if($action->profile_id !== $target_profileId){  
          $postOwner = $post->writer->user;
          $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
          ];
          if ($post->post_type === 'MissingPerson') {
            $postOwner->notify(new MissingPostSaved($notifyArr));
          }else{
            $postOwner->notify(new PostSaved($notifyArr));
          }
        }
      }

      return response()->json($post);
    }

    public function hide(Request $request, $id)
    {
      $post = Post::find($id);
      $target_profileId = $post->profile_id;
      $user = Auth::user();
      $profile = Profile::where('user_id', $user->id)->first();

      $profileId = $profile->id;
      $activityId = $post->activity_id;
      $activity = Activity::find($activityId);
      $activity->hide += 1;
      $activity->save();

      $action = new Action;
      $action->activity_id = $activityId;
      $action->profile_id = $profileId;
      $action->type = 'Post';
      $action->action_type = 'hide';
      $temp = Array();
      $temp[0] = $target_profileId;
      $user_list = json_encode($temp);
      $action->target_profile_id = $user_list;
      $action->save();

      if($action->profile_id !== $target_profileId){
        $postOwner = $post->writer->user;
        $notifyArr = [
          'avatar_path' => $profile->avatar_path,
          'name' => $profile->first_name . ' ' . $profile->last_name
        ];
        if ($post->post_type === 'MissingPerson') {
          $postOwner->notify(new MissingPostHided($notifyArr));
        }else{
          $postOwner->notify(new PostHided($notifyArr));
        }
      }

      return response()->json($post);
    }

    public function like(Request $request, $id)
    {
      $post = Post::find($id);
      $target_profileId = $post->profile_id;
      $liked = $post->liked;
      $user = Auth::user();
      $profile = Profile::where('user_id', $user->id)->first();

      $profileId = $profile->id;
      $activityId = $post->activity_id;
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
        $action->type = 'Post';
        $action->action_type = 'like';
        $temp = Array();
        $temp[0] = $target_profileId;
        $user_list = json_encode($temp);
        $action->target_profile_id = $user_list;
        $action->save();

        if($action->profile_id !== $target_profileId){
          $postOwner = $post->writer->user;
          $notifyArr = [
            'avatar_path' => $profile->avatar_path,
            'name' => $profile->first_name . ' ' . $profile->last_name
          ];
          if ($post->post_type === 'MissingPerson') {
            $postOwner->notify(new MissingPostSupported($notifyArr));
          }else{
            $postOwner->notify(new PostSupported($notifyArr));
          }
        }
      }

      return response()->json($post);
    }

    public function delete($id)
    {
      $attachmentIds = PostAttachment::where('post_id', $id)->pluck('attachment_id')->all();
      $fileUrls = Attachment::whereIn('id', $attachmentIds)->pluck('path')->map(function ($att) {
        return 'public/' . $att;
      })->all();
      Storage::delete($fileUrls);
      PostAttachment::where('post_id', $id)->delete();
      Attachment::whereIn('id', $attachmentIds)->delete();
      
      $activityId = Post::find($id)->activity_id;
      Action::where('activity_id', $activityId)->delete();
      Activity::where('id', $activityId)->delete();
      MissingPost::where('post_id', $id)->delete();
      Post::find($id)->delete();

      $activityIds = Comment::where('post_id', $id)->pluck('activity_id')->all();
      Action::whereIn('activity_id', $activityIds)->delete();
      Activity::whereIn('id', $activityIds)->delete();
      Comment::where('post_id', $id)->delete();

      return response(json_encode(['id' => $id]), 200)->header('Content-Type', 'text/json');
    }
    
  }
