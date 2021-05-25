<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Models\Follow;
use App\Models\Support;

class ProfileController extends Controller
{
  public function posts($profileId) {
    
    return response()->json(Profile::find($profileId)->posts);
  }

  public function store(Request $request, $profileId) {
    // $user_id = Profile::where('id', $profileId)->get()[0]->user_id;
    $user = User::where('id', $profileId)->first();
    if (!$user || !Hash::check($request->oldPwd, $user->password)) {
      return response(json_encode([
        'failer' => ['The provided credentials are incorrect.'],
        ]), 400)->header('Content-Type', 'text/json');
    } else {
      $user->name = $request->name;
      $user->email = $request->email;
      $user->number = $request->phoneNumber;
      $user->bio = $request->bio;
      $user->password = Hash::make($request->newPwd);
      $user->save();
      // return json_encode('success');
      return response(json_encode([
        'res' => ['success'],
        ]), 200)->header('Content-Type', 'text/json');
    }
    
  }

  public function show($profileId) {
    $data = Array();
    $data['followes'] = Follow::where('followes_id', $profileId)->count();
    $data['follower'] = Follow::where('follower_id', $profileId)->count();
    $data['supports'] = Support::where('supportID', $profileId)->count();
    $data['supporter'] = Support::where('supporterID', $profileId)->count();
    $data['user'] = User::find($profileId);
    $data['proximityAlert'] = Profile::where('id', $profileId)->get()[0]->proximityAlert;
    $data['normalAlert'] = Profile::where('id', $profileId)->get()[0]->normalAlert;
    $data['foundAlert'] = Profile::where('id', $profileId)->get()[0]->foundAlert;
    return $data;
  }

  public function update(Request $request) {
    $current_userID = Auth::user()->id;
    $profile = Profile::where('user_id', $current_userID)->get()[0];
    $profile->proximityAlert = $request->proximityAlert;
    $profile->normalAlert = $request->normalAlert;
    $profile->foundAlert = $request->foundAlert;
    return $profile->save();
  }
  
}
