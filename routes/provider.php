<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('test',function(){
    return 'test from truck api';
});

Route::post('register','UsersController@regsiterProvider');



?>