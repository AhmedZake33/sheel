<?php

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
Route::get('profile','UsersController@profile')->middleware('auth:api');

// request 
Route::group(['middleware' => 'auth:api' , 'prefix' => 'request'] , function(){
    
    Route::get('show','RequestsController@show');
    Route::post('cancel','RequestsController@cancel');
    Route::post('accept','RequestsController@accept');
 });




?>