<?php

use App\Http\Controllers\Api\Payments\CardsController;
use App\Http\Controllers\Api\Payments\PromoCodesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UsersController;
use App\Http\Controllers\Api\payments\PaymentsController;
use App\Http\Controllers\Api\Payments\TransactionsController;
use App\Http\Controllers\Api\ReviewsController;

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
   Route::post('{request}/pay','RequestsController@pay');
   Route::post('{request}/manual/pay','RequestsController@manualPay');
});

// payments
Route::group(['prefix' => 'payments' , 'middleware' => 'auth:api' ] , function(){
    Route::post('add/card',[CardsController::class,'addCard']);
    Route::post('edit/card/{card}',[CardsController::class,'editCard']);
    Route::get('cards',[CardsController::class,'cards']);

    Route::post('check/promocode',[PromoCodesController::class , 'check']);
    
    // tap payment
    // Route::get('buy/{id}',[PaymentsController::class , 'buy'])->name('buy');

    // transactions api
    Route::post('transactions/create',[TransactionsController::class , 'createTransaction']);
    Route::get('buy/{transaction}/{card}',[TransactionsController::class , 'buy'])->name('buy');
});

// notification
Route::group(['middleware' => 'auth:api' , 'prefix' => 'notifications'] , function(){
    Route::post('','NotificationsController@index');
});

Route::group(["prefix" => 'profile','middleware' => 'auth:api'] , function(){
    Route::get('','UsersController@profile');
    Route::post('/update/{user}','UsersController@update');
});

Route::get('download/{archive}','ArchiveController@download')->name('download_file');

// chat api
Route::group(["prefix" => 'chats','middleware' => 'auth:api'] , function(){
    Route::post('{request}','ChatsController@get');
    Route::post('/send/{request}','ChatsController@send');
});

// reviews api
Route::group(["prefix" => "reviews" , 'middleware' => "auth:api"] , function(){
    Route::post('/{id}','ReviewsController@add');
});

// lookups api
Route::group(["prefix" => "lookups" , 'middleware' => "auth:api"] , function(){
    Route::get('','LookupsController@get');
});