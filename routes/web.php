<?php

use App\Http\Controllers\Api\Chat\PusherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payments\PaymentsController;
use App\Http\Controllers\api\UsersController;
use App\Models\Notification;
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

// Route::post('/pusher/auth', function (Request $request) {
//     $user = $request->user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $socket_id = $request->input('socket_id');
//     $channel_name = $request->input('channel_name');

//     $pusher = new Pusher\Pusher(
//         env('PUSHER_APP_KEY'),
//         env('PUSHER_APP_SECRET'),
//         env('PUSHER_APP_ID'),
//         [
//             'cluster' => env('PUSHER_APP_CLUSTER'),
//             'useTLS' => true,
//         ]
//     );

//     $presence_data = ['id' => $user->id, 'name' => $user->name];

//     $auth = $pusher->presence_auth($channel_name, $socket_id, $user->id, $presence_data);

//     return response()->json($auth + ['csrf_token' => csrf_token()]);
// })->middleware('auth');


Route::get('/', function () {
    // return ENV("MIX_PUSHER_APP_CLUSTER");
    return view('home');
});

Route::get("test",function(){
    return "test here";
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
    return view("success_payment");
})->name('success');

Route::get('fail',function(){
    return view("fail_payment");
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
    $request = App\Models\Request::find(173);
});



Route::get("send-chat",function(){
    try {
        // $user = App\Models\User::find(48);
        // return $user->createToken('My Token')->accessToken;
        $notification = DB::table("notifications")->where('id',127)->first();
        // return ($notification);
        // Broadcast(new \App\Events\NotificationEvent($notification));
        Broadcast(new \App\Events\ChatMessageEvent('$notification'));
    } catch(\Exception $ex){
        return $ex->getMessage();
    }
  
    return "true";
});


Route::get('/broadcasting/auth', 'UsersController@authenticate');
