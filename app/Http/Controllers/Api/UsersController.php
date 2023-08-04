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
use App\Models\System\System;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersController extends Controller
{

    public function register(registerRequest $request)
    {
     
        // validate the data 
        $validated = $request->validated();
       
         // register user and create otp 
        $user = User::create($validated);
        $user->secret = Str::random(50);
        $user->save();
        User::createOtp($user , true);


        // send otp to user mobile phone and verify by email 

        $message = ($this->lang == 'en')? "Successfully Please Check Your Mobile Phone To Complete Registeration" : "تم بنجاح , يرجى التحقق من هاتفك المحمول لإكمال التسجيل";
        // return response
        return success($user->data(System::DATA_BRIEF) , System::HTTP_OK , $message);
    }   

    public function login(loginRequest $request)
    {
        // validate data
       $mobile =  $request->validated()['mobile'];
       $message = null;
        // try find user by phone 
        $user = User::where('mobile',$mobile)->first();
        
        // create otp code
        if($user && $user->status == User::STATUS_ACTIVE){
            User::createOtp($user);
        }else{
            $message = ($this->lang == 'en') ? 'Please Complete Verification' : '  برجاء اكمال التحقق من البيانات  ';
            return success([],System::HHTP_Unprocessable_Content,$message);
        }
        

        // send otp to user mobile phone
        $message = ($this->lang == 'en')?  'Success Check Your mobile Phone To Complete Login' : 'تم بنجاح , تحقق من هاتفك المحمول لإكمال تسجيل الدخول';
        // return response
        return success(['secret' => $user->secret , 'opt_code' => $user->otp_code],System::HTTP_OK,$message);
    } 
    
    public function resendCode(loginRequest $request)
    {
        // validate data
       $mobile =  $request->validated()['mobile'];

       // try find user by phone 
       $user = User::where('mobile',$mobile)->orWhere('temp_mobile',$mobile)->first();
       
       // create otp code
       User::createOtp($user);

       // send otp to user mobile phone

        $message = ($this->lang == 'en')?  'Success resend Code Again':'تم إرسال الكود بنجاح مرة اخري' ;
       // return response
       return success([],System::HTTP_OK,$message);
    }

    public function verifyCode(verifyRequest $request)
    {
        $validated = $request->validated();
        $message = null;
        $otp_code = $validated['otp_code'];
        $secret = $validated['secret'];
        if($secret && $otp_code){
            $user = User::where('secret',$secret)->where('otp_code',$otp_code)->first();
            if($user){
                $mobile = 'mobile';
                $user->verify($mobile);
                // if($user->status != User::STATUS_ACTIVE){
                //     $message = ($this->lang == 'en')?  'success mobile Verification please Complete Email Verification':'تم بنجاح التحقق من الهاتف المحمول ، يرجى إكمال التحقق من البريد الإلكتروني' ;
                //     return success([],System::HTTP_OK , $message);
                // }
                $data = $user->data(System::DATA_DETAILS);
                $token = $user->createToken('My Token')->accessToken;
                $data->token = $token;
                $message = ($this->lang == 'en')? 'successfully completed ' : 'مكتملة بنجاح' ;
                return success($data,System::HTTP_OK ,$message);
            }else{
                $message = ($this->lang == 'en')? 'Code Is Invalid':'الكود خاطئ';
                return success([],System::HHTP_Unprocessable_Content , $message);
            }
        }
        $message = ($this->lang == 'en')? 'Invalid Inputs' :  'البياتات المدخلة غير صحيحة';
        return success(System::ERROR_INVALID_INPUT,$message , []) ;
    }

    public function verifyEmail($secret , $slug)
    {  
        
        $message = null;
        if($secret &&  $slug){
            $user = User::where('secret',$secret)->where('slug' , $slug)->first();
        
            if($user){
                $email = 'email';
                $user->verify($email);
                $user->remove();
                $message = ($this->lang == 'en') ? 'Successfully Verify Email' : 'تم التحقق من البريد الالكتروني بنجاح';
                return success([],System::HTTP_OK , $message);
            }
            $message = ($this->lang == 'en') ? 'Not Found Data' : 'البيانات غير موجودة';
            return success([],System::HHTP_Unprocessable_Content , $message);
            
        }
        $message = ($this->lang == 'en') ? 'Not Found Data' : 'البيانات غير موجودة';
        return success([],System::HHTP_Unprocessable_Content , $message);


    }
}
