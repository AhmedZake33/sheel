<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\registerProvider;
use App\Http\Requests\ResendCodeRequest;
use App\Http\Requests\verifyRequest;
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
        return $this->provider->createProvider($request);
        return 'success';
    }

    public function resendCode(ResendCodeRequest $request)
    {
        return $this->user->resendCode($request);
    }

    public function verifyCode(verifyRequest $request)
    {
        return $this->provider->verifyCode($request);
    }

    public function login(loginRequest $request)
    {
        return $this->user->login($request);
    }

    public function profile()
    {
        return auth()->user();;
        return $this->user->profile();
    }

    public function verifyEmail($secret , $slug)
    {  
        return $this->user->verifyEmail($secret , $slug);
    }
}