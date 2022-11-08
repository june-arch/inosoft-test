<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('basic.authentication')->get('hello', function (Request $request){
    return "hello world";
});
Route::post('v1/users/login', [UserController::class, 'authenticate']);
Route::post('v1/users/register', [UserController::class, 'register']);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('v1/users/logout', [UserController::class, 'logout']);
    Route::resource('v1/users', UserController::class)->only([
        'destroy', 'show', 'store', 'update', 'index'
    ]);
});
