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
        $profile = Auth::user()->profile;
        return Action::ofList($profile->id)->ofUser($profile->id)->ofVerify()->with(['activity', 'post', 'comment', 'profile'])->orderBy('updated_at', 'desc')->get();        
    }
}