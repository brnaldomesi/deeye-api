<?php

namespace App\Http\Controllers\Api\v1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;

class UserController extends Controller
{
	public function checkUser(Request $request)
	{
		$request->validate([
			'email' => 'required|email'
		]);

		$user = User::where('email', $request->email)->first();
		
		if($user) {
			return response()->json(['status' => 200, 'msg' => 'Email exist']);
		} else {
			return response()->json(['status' => 404, 'msg' => 'Email not exist']);
		}
	}

	public function signup(Request $request)
	{
		$rules = [
			'email' => 'required|email',
			'password' => 'required',
			'deviceName' => 'required'
		];
		$validator = Validator::make($request->all(), $rules);
		
		if ($validator->fails()) {
				return response($validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
		}

		if (User::where('email', $request->email)->count() > 0)
		{
			return response()->json(['status' => 400, 'msg' => 'Email exist']);
		}

		$user = new User;
		$user->email = $request->email;
		$user->password = Hash::make($request->password);
    if($request->fcmToken) {
      $user->fcm_token = $request->fcmToken;
    }
		$user->save();

		$profile = new Profile;
		$profile->user_id = $user->id;
    $profile->first_name = 'User';
    $profile->last_name = $user->id;
		$profile->save();

		return response()->json(['auth-token' => $user->createToken($request->deviceName)->plainTextToken, 'profile' => $profile]);
	}

	public function login(Request $request)
	{
		$rules = [
			'email' => 'required|email',
			'password' => 'required',
			'deviceName' => 'required'
		];
		$validator = Validator::make($request->all(), $rules);
		
		if ($validator->fails()) {
				return response($validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
		}

		$user = User::where('email', $request->email)->first();
    
    
		if (! $user || ! Hash::check($request->password, $user->password)) {
      return response(json_encode([
        'email' => ['The provided credentials are incorrect.'],
        ]), 400)->header('Content-Type', 'text/json');
    }
    if($request->fcmToken) {
      $user->update(['fcm_token' => $request->fcmToken]);
    }
    
		return response()->json(['auth-token' => $user->createToken($request->deviceName)->plainTextToken, 'profile' => $user->profile ]);
  }
  
  public function posts($userId) {
    
    return response()->json(User::find($userId)->profile->posts);
  }

  public function location(Request $request) {
	$id = Auth::user()->id;
	$user = User::find($id);
	$user->latitude = $request->latitude;
	$user->longitude = $request->longitude;
	return $user->save();
  }
}
