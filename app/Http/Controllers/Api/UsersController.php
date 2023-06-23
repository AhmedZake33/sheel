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
use App\Models\System\System;
use Illuminate\Support\Str;

class UsersController extends Controller
{

    public function register(registerRequest $request)
    {
     
        // validate the data 
        $validated = $request->validated();
       
         // register user and create otp 
        $otp = rand(10000,99999);
        $user = User::create($validated);
        $user->secret = Str::random(50);
        $user->otp_code = $otp;
        $user->otp_time = now();
        $user->save();

        // create and send otp to user 
        

        // return response
        return success($user->data(System::DATA_BRIEF) , System::HTTP_OK , 'Successfully Please Check Your Mobile Phone To Complete Registeration');
    }   

    public function verify($secret)
    {   
       if($secret){
        $user = User::where('secret',$secret)->first();
    
        if($user){
            $user->otp_code = rand(10000,99999);
            $user->otp_time = now();
            $user->save();
        }

        return success([],System::HTTP_OK , 'Successfully Please Check Your Mobile Phone To Complete registeration');
        
       }

    }

    public function verifyCode(verifyRequest $request)
    {
        $validated = $request->validated();
        $otp_code = $validated['otp_code'];
        $secret = $validated['secret'];
        if($secret && $otp_code){
            $user = User::where('secret' , $secret)->where('otp_code',$otp_code)->first();
            if($user){
                $data = $user->data(System::DATA_DETAILS);
                $token = $user->createToken('My Token')->accessToken;
                return success($token,System::HTTP_OK , 'Successfully');
            }else{
                return success([],System::HHTP_Unprocessable_Content , 'Code Is Invalid');
            }
            return $user;
        }
        return $code;
    }

    public function login(Request $request)
    {
        // User::create([
        //     'name' => 'zaki',
        //     'password' => Hash::make(12345678),
        //     'email' => 'ahmed.zake333@gmail.com'
        // ]);
        // validate the data 
        $user = User::find(4);
        // return $user;
        $token = $user->createToken('MyAppToken')->accessToken;

        return response()->json($token);



        // try find user by phone 


        // if all is true create token and return with data

    }   
}
