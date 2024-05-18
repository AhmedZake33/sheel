<?php

use App\Http\Controllers\Provider\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('test',function(){
    return 'test from truck api';
});

Route::post('register','UsersController@regsiterProvider');
Route::post('resendCode','UsersController@resendCode');
Route::post('verifyCode','UsersController@verifyCode');
Route::get('verifyEmail/{secret}/{slug}','UsersController@verifyEmail');
Route::post('login','UsersController@login');

Route::group(["prefix" => "user" , "middleware" => "auth:api"], function(){
    Route::get('profile','UsersController@profile');
    Route::post('/update','UsersController@update');
    Route::post("activate",'UsersController@activeNow');
    Route::post("change-location","UsersController@changeLocation");
});
Route::get('user/activate/{userSecret}','UsersController@activate');

// request 
Route::group(['middleware' => 'auth:api' , 'prefix' => 'request'] , function(){
    
    Route::get('show','RequestsController@show');
    Route::post('cancel','RequestsController@cancel');
    Route::post('accept','RequestsController@accept');
    Route::post("current",'RequestsController@current');
    Route::get("history","RequestsController@history");
    Route::post("{id}/pick-up","RequestsController@pickUp");
    Route::post("{id}/arrived","RequestsController@arrived");
 });


// lookups api
Route::group(["prefix" => "lookups" , 'middleware' => "auth:api"] , function(){
    Route::get('','LookupsController@get');
});

// reviews api
Route::group(["prefix" => "reviews" , 'middleware' => "auth:api"] , function(){
    Route::post('/{id}','ReviewsController@add');
});


Route::post('/broadcasting/auth', 'UsersController@authenticate');


?>