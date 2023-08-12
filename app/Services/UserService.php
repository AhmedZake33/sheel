<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\System\System;
use Carbon\Carbon;

class UserService extends Base
{

    public function register($request)
    {
        // validate the data 
        $validated = $request->validated();
        $message = ($this->lang == 'en')? "Successfully Please Check Your Mobile Phone To Complete Registeration" : "تم بنجاح , يرجى التحقق من هاتفك المحمول لإكمال التسجيل";
        
        // return $validated['email'];
        // check if not complete registeration cycle 

        if($this->checkIncomplelteUserFound($validated)){
            $user = $this->checkIncomplelteUserFound($validated);
            User::createOtp($user);
            return success($user->data(System::DATA_BRIEF) , System::HTTP_OK , $message);
        }

        if($this->checkUserFound($validated)){
            $message = ($this->lang == 'en') ? 'User Already found Please Login' : '  المستخدم موجود بالفعل برجاء تسجيل الدخول' ;
            return success([],System::HHTP_Unprocessable_Content,$message);
        }
       
         // register user and create otp 
        $user = $this->createUser($validated);
 
        $user->remove();

        // return response
        return success($user->data(System::DATA_BRIEF) , System::HTTP_OK , $message);
    }

    public function checkUserFound($data)
    {
        // check if user found 
        $user = User::where('email',$data['email'])->orWhere('mobile',$data['mobile'])->first();
        if($user){
            return $user;
        }
        return false;
    }

    public function checkIncomplelteUserFound($data)
    {
        // check if user found 
        $user = User::Where('email', $data['email'])->where('mobile',$data['mobile'])->where('status',User::STATUS_INCOMPLETE)->first();
        if($user){
            return $user;
        }
        return false;
    }


    public function profile()
    {
        $user =  auth()->user();
        $data = (object)[];
        $data->name = $user->name;
        $data->mobile = $user->mobile;
        $data->email = $user->email;
        $data->secret = $user->secret;
        $data->email_verification = $user->email_verification;
        $data->status = $user->status;


        return success($data , System::HTTP_OK , 'success');

    }

    public function createUser($validated)
    {
        $user = User::create($validated);
        $user->secret = Str::random(50);
        $user->save();
        User::createOtp($user , true);
        return $user;
    }

    public function login($request)
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

    public function resendCode($request)
    {
         // validate data
       $mobile =  $request->validated()['mobile'];

       // try find user by phone 
       $user = User::where('mobile',$mobile)->first();
       
       // create otp code
       User::createOtp($user);

        $message = ($this->lang == 'en')?  'Success resend Code Again':'تم إرسال الكود بنجاح مرة اخري' ;
       // return response
       return success(['secret' => $user->secret],System::HTTP_OK,$message);
    }

    public function verifyCode($request)
    {
        $validated = $request->validated();
        $message = null;
        $otp_code = $validated['otp_code'];
        $secret = $validated['secret'];
        if($secret && $otp_code){
            // current time 
            $desiredTime = Carbon::now()->addMinutes(-5);
            $user = User::where('secret',$secret)->where('otp_code',$otp_code)->where('otp_time', '>=',$desiredTime)->first();
            //  dd(Carbon::now()->addMinutes(5)->diffInMinutes(carbon::parse('2023-08-07 21:07:49')));
            if($user){
                $mobile = 'mobile';
                $user->verify($mobile);
                $data = $user->data(System::DATA_DETAILS);
                $token = $user->createToken('My Token')->accessToken;
                $data->token = $token;
                $message = ($this->lang == 'en')? 'successfully completed ' : 'مكتملة بنجاح' ;
                return success($data,System::HTTP_OK ,$message);
            }else{
                $message = ($this->lang == 'en')? 'Data Is invalid':'الكود خاطئ';
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

?>