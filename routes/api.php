<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\v1\Auth\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('auth')->group(function () {
  Route::post('/checkUser', [UserController::class , 'checkUser']);
  Route::post('/login', [UserController::class , 'login']);
  Route::post('/signup', [UserController::class , 'signup']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::get('/posts', [PostController::class , 'index']);
});
