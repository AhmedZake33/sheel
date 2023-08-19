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
}
