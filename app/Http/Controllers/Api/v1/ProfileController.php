<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Profile;

class ProfileController extends Controller
{
  public function posts($profileId) {
    
    return response()->json(Profile::find($profileId)->posts);
  }
}
