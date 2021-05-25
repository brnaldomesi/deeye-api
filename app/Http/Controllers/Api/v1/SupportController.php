<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Support;

class SupportController extends Controller
{
    public function store(Request $request, $profileID) 
    {
        $current_userID = Auth::user()->id;
        $supporterID = Profile::where('user_id', $current_userID)->get()[0]->id;
        $support = new Support;
        $support->supporterID = $supporterID;
        $support->supportID = intval($profileID);
        $support->detail = $request->detail;
        return $support->save();
    }
}
