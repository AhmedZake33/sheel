<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\registerProvider;
use App\Http\Requests\ResendCodeRequest;
use App\Http\Requests\verifyRequest;
use App\Models\System\System;
use App\Services\ProviderService;
use App\Services\UserService;
use App\Http\Requests\ProfileRequest;
use App\Models\Notification;
use App\Models\Request as ModelsRequest;
use Illuminate\Support\Arr;
use Pusher\Pusher;
use Auth;

class UsersController extends Controller 
{

    protected $provider = null;
    protected $user = null;

    public function __construct(ProviderService $provider , UserService $user)
    {
        $this->provider = $provider;
        $this->user = $user;
    }
    public function regsiterProvider(registerProvider $request)
    {
        return $this->provider->createProvider($request);
    }

    public function verifyCode(verifyRequest $request)
    {
        return $this->provider->verifyCode($request);
    }

    public function resendCode(ResendCodeRequest $request)
    {
        return $this->user->resendCode($request);
    }

    public function login(loginRequest $request)
    {
        return $this->user->login($request , User::TYPE_PROVIDER);
    }

    public function profile()
    {
        return $this->user->profile(User::TYPE_PROVIDER);
    }

    public function verifyEmail($secret , $slug)
    {  
        return $this->user->verifyEmail($secret , $slug);
    }

    public function activate($userSecret)
    {
        $user = User::where('secret',$userSecret)->first();
        if($user){
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            return success([],System::HTTP_OK , "SUCCESS ACTIVIATE ACCOUNT");
        }
        
    }

    public function update(ProfileRequest $request)
    {
        // return $user;
        $user = auth()->user();
        if(!$user){
            return error([],404,"user Not Found");
        }
        $validated = $request->validated();
        if($request->profile_photo){
            if($user->archive->findChildByShortName('profile_photo')){
                $user->archive->findChildByShortName('profile_photo')->delete();
                $user->archive->addDocumentWithShortName($request->profile_photo , null , 'profile_photo' , 'profile_photo');
            }else{
                $user->archive->addDocumentWithShortName($request->profile_photo , null , 'profile_photo' , 'profile_photo');
            }
        }

        $user->update(Arr::except($validated , ['email','profile_photo']));
        if(array_key_exists('email',$validated) && $user->email != $validated['email']){
            $user->email = $validated['email'];
            $user->email_verification =  User::STATUS_INCOMPLETE;
            $user->save();

            // send email by mail server
        }
        $message = ['ar' => 'تم التعديل بنجاح' , 'en' => 'profile updated successfully'][app()->getLocale()];
        return success([],System::HTTP_OK , $message);
    }

    public function activeNow(Request $request)
    {

        $user = User::find(auth()->id());
        $provider = $user->provider;
        
        $provider->lat = $request->lat;
        $provider->lng = $request->lng;
        $provider->save();

        if($user){
            $provider =  $user->provider;
            // update provider
            $provider->active = !($provider->active);
            $provider->save();
            return success($user->data(System::DATA_LIST),System::HTTP_OK,"successs");
        }
        

    }

    public function authenticate(Request $request)
    {
        // $socketId = '180146.66614478';
        // $channelName = "privateNotification.4";

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        

        // Authenticate the user and generate authorization data
        // You may need to replace this logic with your own authentication and authorization logic
        // $userId = auth()->user()->id; // Assuming you're using Laravel's built-in authentication

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        $authData = $pusher->socket_auth($channelName, $socketId);
        // $authData = $pusher->socket_auth('', '');

        return response()->json(json_decode($authData));
    }

    public function changeLocation(Request $request)
    {
        $user = Auth::user();
        $provider =  $user->provider;
        $provider->lat = $request->lat;
        $provider->lng = $request->lng;
        $provider->save();

        $currectAcceptedRequest =  $provider->requestsProviders(1)->first();

        // fire events here
        if($currectAcceptedRequest){
            $request = ModelsRequest::find($currectAcceptedRequest->request_id);
            event(new \App\Events\CurrentRequests($request->user_id , $request));
            $notification = Notification::find(1);
            event(new \App\Events\NotificationEvent($notification));
        }

        return success([],System::HTTP_OK);
    }
}