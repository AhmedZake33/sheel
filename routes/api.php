<?php

use App\Http\Controllers\Api\Payments\CardsController;
use App\Http\Controllers\Api\Payments\PromoCodesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UsersController;
use App\Http\Controllers\Api\payments\PaymentsController;
use App\Models\Payment\PromoCode;

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
   Route::post('accept','RequestsController@accept');
   Route::post('cancel','RequestsController@cancel');
});

// payments
Route::group(['prefix' => 'payments' , 'middleware' => 'auth:api'] , function(){
    Route::post('add/card',[CardsController::class,'addCard']);
    Route::post('edit/card/{card}',[CardsController::class,'editCard']);

    Route::post('check/promocode',[PromoCodesController::class , 'check']);
});

// notification
Route::group(['middleware' => 'auth:api' , 'prefix' => 'notifications'] , function(){
    Route::post('','NotificationsController@index');
});

Route::group(['middleware' => 'auth:api'] , function(){
    Route::get('profile','UsersController@profile');
});

Route::get('download/{archive}','ArchiveController@download')->middleware('auth:api')->name('download_file');
