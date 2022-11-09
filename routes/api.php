<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\TransactionController;

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

Route::get('hello', function (Request $request){
    return "hello world";
});

Route::group(['middleware' => ['basic.authentication']], function (){
    Route::post('v1/users/login', [UserController::class, 'authenticate']);
    Route::post('v1/users/register', [UserController::class, 'register']);
    Route::resource('v1/users', UserController::class)->only([
        'destroy', 'show', 'store', 'update', 'index'
    ]);
    Route::resource('v1/transaction', TransactionController::class)->only([
        'destroy', 'show', 'store', 'update', 'index'
    ]);
    Route::resource('v1/kendaraan', KendaraanController::class)->only([
        'destroy', 'show', 'store', 'update', 'index'
    ]);
});

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('v1/users/logout', [UserController::class, 'logout']);
    Route::get('v1/kendaraan/available', [KendaraanController::class, 'available']);
    Route::post('v1/transaction/order', [TransactionController::class, 'order']);
    Route::get('v1/transaction/history', [TransactionController::class, 'history']);
});
