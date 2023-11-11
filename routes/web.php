<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payments\PaymentsController;
use App\Models\Payment\Transaction;
use App\Services\StripeService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('test',function(){
    return app()->version();
});

Route::get('payments',function(){
    return view('payments');
});

Route::get('transaction',function(){
    return Transaction::find(1)->user;
});

Route::get('addCard',function(){
    // $object = (object)[];
    // $object->name = 'zake';
    // $object->id = 12;
    // return fetchTransaction( $object);
    return view('addCard');
});

// Route::get('buy',[PaymentsController::class,'buy'])->name('buy');

Route::get('callback/{transaction}',[PaymentsController::class , 'callBack'])->name('callback');

Route::get('stripe',function(){
    $StripeService = new StripeService();
    return $StripeService->createCharge();
    return 'success';
});