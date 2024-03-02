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
        // return $this->provider->createProvider($request);
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
        return $this->user->profile();
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
}