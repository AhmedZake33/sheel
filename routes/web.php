<?php

use App\Http\Controllers\Api\Chat\PusherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payments\PaymentsController;
use App\Http\Controllers\Api\UsersController;
use App\Mail\ExampleMail;
use App\Models\Notification;
use App\Models\Payments\Payment;
use App\Models\Payments\Transaction;
use App\Services\StripeService;
use Illuminate\Http\Request;
use App\Models\Request as Requestmodel;
use App\Services\LocationService;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Mail;

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
    // return domain(); 
    // return ENV("MIX_PUSHER_APP_CLUSTER");
    return view('home');
});

Route::get('fire-event', function () {
    // $request = Requestmodel::find(172);
    event(new \App\Events\PublicEvent());
    // event(new \App\Events\CurrentRequests(76 , $request));
    // return event(new \App\Events\RequestEvent($request,1));

    // event(new \App\Events\PublicEvent());

    // $notification = Notification::find(127);
    // event(new \App\Events\NotificationEvent($notification));


    // fire request event
    // event(new \App\Events\RequestEvent($request));
    // event(new \App\Events\CurrentRequests($nearestLocation->user_id , $this));

    return "success";
});

Route::get("create-token/{id}",function($id){
    $user = \App\Models\User::find($id);
    $token = $user->createToken('My Token')->accessToken;
    return $token;
});

Route::get("test",function(){
    return "test here";
});

Route::get('/login/{token?}', function ($token = null) {
    return view('login')->with(["token" => $token]);
})->name('login');

Route::get('/chat/{id}',function($id){
    return view('chat')->with(['id' => $id]);
});

Route::post('sendMessage/{id}',[UsersController::class ,'sendMessage'])->name('sendMessage');

Route::get('/home', function () {
    return view('home');
})->name('home');


route::post("verify",[UsersController::class , "verify"])->name("verify");
route::post('login',[UsersController::class , 'loginWithEmail'])->name('login');
route::post('loginWithToken',[UsersController::class , 'loginWithToken'])->name('loginWithToken');
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

Route::get('callback',[PaymentsController::class , 'callBack'])->name('callback');
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
    // $card = \App\Models\Payments\Card::find(28); 
    // return \App\Services\TapService::createTokenFromCard($card->id);
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

Route::get("review-provider",function(){
    if(!Auth::user()){
        return view('home');
    }
    $providers = \App\Models\User::where("type",\App\Models\User::TYPE_PROVIDER)
    ->where("status",\App\Models\User::STATUS_PENDING_PROVIDER)
    ->get()->transform(function($provider){
        $data = $provider; 
        $data->profile = $provider->files(\App\Models\User::TYPE_PROVIDER);
        return $data;
    });

    // dd($providers[2]->profile);
    // dd(get_object_vars(($providers[0]->profile)[0]));

    return view("reviewProvider")->with(["providers" => $providers]);
})->name('review-provider');

Route::post("accept-provider/{provider}","UsersController@acceptProvider")->name("accept-provider");
Route::post("refuse-provider/{provider}","UsersController@refuseProvider")->name("refuse-provider");

Route::get('/broadcasting/auth', 'UsersController@authenticate');


Route::get("check-time",function(){
    $notification = Notification::find(1);
    return $notification->data();
});

Route::get("stripe",function(){
    $stripe = new StripeService();
    return $stripe->createCharge();
});


Route::get("payment",function(){
    return view('payment');
});

Route::get("stripe",function(){
    $payment= Payment::find(149);
    // return $payment->amount;
    return StripeService::pay($payment);
});

Route::get("/sms",function(){
    $message = "hello";
    $number = +201116028622;

    try {
        $twilio = new TwilioService();
        // $twilio->sendSms($number , $message);
        return $twilio->send();
        return "success sent message";
    } catch(\Exception $ex){
        return $ex->getMessage();
    }

    return "success";
});

Route::get("send-mail",function(){
    $details = [
        'title' => 'Mail from My Laravel App',
        'body' => 'This is a test email sent using Gmail SMTP in Laravel.'
    ];

    try{
        Mail::to('ahmed.zake333@gmail.com')->send(new ExampleMail($details));
    }catch(\Exception $ex){
        return $ex->getMessage();
    }
});

Route::get("test-payment",function(){
    return Payment::createAndUpdate([]);
});

Route::get("assign-requests",function(){
    $requestsNotAssignProvider = Requestmodel::select("requests.*")
    ->leftJoin("requests_providers","requests_providers.request_id","requests.id")
    ->whereNull("requests_providers.request_id")
    ->get();

    return ($requestsNotAssignProvider);

    foreach($requestsNotAssignProvider as $requestNotAssignProvider){
        $startTime = microtime(true);
        $requestNotAssignProvider->startFindProvider();
        $endTime = microtime(true);
        d($endTime - $startTime);
    }
});