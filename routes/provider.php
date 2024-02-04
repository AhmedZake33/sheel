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

Route::group(["prefix" => "user" , "middleware" => "auth:api"], function(){
    Route::get('profile','UsersController@profile');
    Route::get('activate/{userSecret}','UsersController@activate');
});

// request 
Route::group(['middleware' => 'auth:api' , 'prefix' => 'request'] , function(){
    
    Route::get('show','RequestsController@show');
    Route::post('cancel','RequestsController@cancel');
    Route::post('accept','RequestsController@accept');
 });




?>