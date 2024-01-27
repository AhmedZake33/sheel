<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Auth;
use App\Http\Requests\registerRequest;
use App\Http\Requests\verifyRequest;
use App\Http\Requests\loginRequest;
use App\Http\Requests\ResendCodeRequest;
use App\Models\System\System;
use App\Services\UserService;
use Carbon\Carbon;
use App\Http\Requests\ProfileRequest;
use Exception;
use Illuminate\Support\Arr;
use App\Models\Request as RequestModel;
use App\Models\Chat;
use App\Events\ChatEvent;
use Pusher\Pusher;
use Illuminate\Support\Facades\Broadcast;



class UsersController extends Controller
{
    protected $service;
 
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function register(registerRequest $request)
    {
        return $this->service->register($request);
    }   

    public function login(loginRequest $request)
    {
        return $this->service->login($request);
    } 
    
    public function resendCode(ResendCodeRequest $request)
    {
       return $this->service->resendCode($request);
    }

    public function verifyCode(verifyRequest $request)
    {
        return  $this->service->verifyCode($request);
    }

    public function verifyEmail($secret , $slug)
    {  
        return $this->service->verifyEmail($secret , $slug);
    }

    public function profile()
    {
        return $this->service->profile();
    }

    public function update(ProfileRequest $request , User $user)
    {
        $validated = $request->validated();
        $user = auth()->user();
        // return $user;
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
        $message = ['ar' => 'تم التعديل بنجاح' , 'en' => 'profile updated successfully'][$this->lang];
        return success([],System::HTTP_OK , $message);
    }

    public function loginWithEmail(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){
            // event(new ChatEvent("welcome here from controller" , 2));
            if(Auth::loginUsingId($user->id)){
                return redirect()->route('home');
            }
        }
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function sendMessage(Request $request , $id)
    {
        // return auth()->user();
        $ReceivingUser = RequestModel::find($id)->getReceivingUser();
        // return $ReceivingUser;
        $message = new Chat();
        $message->request_id = $id;
        $message->received_id = $ReceivingUser;
        $message->user_id = Auth::id();
        $message->message = $request->message;
        $message->save();

        \App\Events\testEvent::dispatch(RequestModel::find($id) ,$request->message);
        // broadcast(new \App\Events\testEvent(RequestModel::find($id) ,$request->message))->toOthers();

        return $message;
    }
}   

