<?php

namespace App\Http\Controllers\Api\v1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
      'deviceName' => 'required'
    ]);

    $user = new User;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['auth-token' => $user->createToken($request->deviceName)->plainTextToken ]);
  }

  public function login(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
      'deviceName' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
      throw ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
      ]);
    }

    return response()->json(['auth-token' => $user->createToken($request->deviceName)->plainTextToken ]);
  }
}
