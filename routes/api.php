<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UsersController;

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

Route::get('test',function(){
    return 'test';
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test',function(){
    return 'test';
});

// user request 
Route::post('register','UsersController@register');
Route::post('login','UsersController@login');
Route::get('verifyEmail/{secret}/{slug}','UsersController@verifyEmail');
Route::post('verifyCode','UsersController@verifyCode');
Route::post('resendCode','UsersController@resendCode');

// request 
Route::group(['middleware' => 'auth:api' , 'prefix' => 'request'] , function(){
   Route::post('create','RequestsController@create');
   Route::get('nearestLocations','RequestsController@nearestLocations');
   Route::get('nearestLocation','RequestsController@nearestLocation');
   Route::get('show','RequestsController@show');
   Route::post('cancel','RequestsController@cancel');
});

Route::group(['middleware' => 'auth:api'] , function(){
    Route::get('profile','UsersController@profile');
});

Route::get('download/{archive}','ArchiveController@download')->middleware('auth:api')->name('download_file');




