<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\System\System;
use Carbon\Carbon;

class ProviderService extends Base
{
    public function createProvider($request)
    {
         // after validate all data filter data only 
        $user = User::create($request->except(['emairate_id_front','emairate_id_back','drive_photo','RTA_card_front','RTA_card_back','vehicle_registration_form']));
        $user->secret = Str::random(50);
        $user->save();
         // add files
         $user->archive->addDocumentWithShortName($request->emairate_id_front , null , 'emairate_id_front' , 'emairate_id_front');
         $user->archive->addDocumentWithShortName($request->emairate_id_back , null , 'emairate_id_back' , 'emairate_id_back');
         $user->archive->addDocumentWithShortName($request->drive_photo , null , 'drive_photo' , 'drive_photo');
         $user->archive->addDocumentWithShortName($request->RTA_card_front , null , 'RTA_card_front' , 'RTA_card_front');
         $user->archive->addDocumentWithShortName($request->RTA_card_back , null , 'RTA_card_back' , 'RTA_card_back');
         $user->archive->addDocumentWithShortName($request->vehicle_registration_form , null , 'vehicle_registration_form' , 'vehicle_registration_form');
         
         // draft data
         $user->draft();
         
        // create provider record 
        $provider = new Provider();
        $provider->user_id = $user->id;
        $provider->service_id = $request->service_id;
        $provider->save();
        // create opt 
        User::createOtp($user ,true);
        
        $message =  ($this->lang == 'ar')? 'تم التسجيل بنجاح'  : "Register Complete Successfully";
        return success($user->data(System::DATA_BRIEF) , System::HTTP_OK , $message);
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
                if($user->status == User::STATUS_ACTIVE){
                    // create token and become provider in system
                    $data = $user->data(System::DATA_DETAILS);
                    $token = $user->createToken('My Token')->accessToken;
                    $data->token = $token;
                    $user->verify('mobile');
                    $message = ($this->lang == 'en')? 'successfully completed ' : ' مكتملة بنجاح ' ;
                    return success($data,System::HTTP_OK ,$message);   

                }
                $user->verify('mobile' , User::STATUS_PENDING_PROVIDER);
                $message = ($this->lang == 'en')? 'successfully completed Please wait to Activate Your Account' : ' مكتملة بنجاح برجاء الانتظار حتي تفعيل الحساب' ;
                return success([],System::HTTP_OK ,$message);
            }else{
                $message = ($this->lang == 'en')? 'Data Is invalid':'الكود خاطئ';
                return success([],System::HHTP_Unprocessable_Content , $message);
            }
        }
        $message = ($this->lang == 'en')? 'Invalid Inputs' :  'البياتات المدخلة غير صحيحة';
        return success(System::ERROR_INVALID_INPUT,$message , []) ;
    }

    public function acceptProvider(Provider $provider)
    {
        // verify user mobile
        $user_id = $provider->user_id;
        $user = User::find($user_id);
        $user->verify('mobile');
        
    }
}