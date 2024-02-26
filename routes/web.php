<?php

use App\Http\Controllers\Api\Chat\PusherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payments\PaymentsController;
use App\Http\Controllers\api\UsersController;
use App\Models\Payments\Transaction;
use App\Services\StripeService;
use Illuminate\Http\Request;

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

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/chat/{id}',function($id){
    return view('chat')->with(['id' => $id]);
});

Route::post('sendMessage/{id}',[UsersController::class ,'sendMessage'])->name('sendMessage');

Route::get('/home', function () {
    return view('home');
})->name('home');

route::post('login',[UsersController::class , 'loginWithEmail'])->name('login');
route::post('logout',[UsersController::class , 'logout'])->name('logout');


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

Route::get('index',function(){
    return view('index');
});

// Route::post('sendMessage',[PusherController::class , "sendMessage"])->name('sendMessage');
Route::get('send',[PusherController::class , "send"]);

// Route::get('buy',[PaymentsController::class,'buy'])->name('buy');

Route::get('callback/{transaction}',[PaymentsController::class , 'callBack'])->name('callback');
Route::get('callbackSavedCard/{transaction}',[PaymentsController::class , 'callbackSavedCard'])->name('callbackSavedCard');
Route::get('success',function(){
    return "success payment";
})->name('success');

Route::get('fail',function(){
    return "fail payment";
})->name('fail');

Route::get('stripe',function(){
    $StripeService = new StripeService();
    return $StripeService->createCharge();
    return 'success';
});


Route::get('showEvent/{requestId}',function($requestId){
    return view('ShowEvent',compact('requestId'));
});

Route::get('sendEvent/{requestId}',function($requestId){
    \App\Events\testEvent::dispatch('welcome' , $requestId);
});

Route::get('createToken',function(){
    $card = \App\Models\Payments\Card::find(28); 
    return \App\Services\TapService::createTokenFromCard($card->id);
    // return $card;
   return domain();
});

Route::get('checkPassword',function(){
    $password = "123445sds";
    $request = new Request();
    // dd($request);
    $request['password'] = $password;
    // $request->save();
    // return $request;
    $request->validate([
        'password' => 'email',
    ]);

    if($request->validate(['password' => 'email'])){
        dd('fail');
    }else{
        dd('success');
    }

    // $request->validate([
    //     'title' => 'required|unique:posts|max:255',
    //     'body' => 'required',
    // ]);
    // return $password;
});

Route::get("test",function(){
   return domain();
});