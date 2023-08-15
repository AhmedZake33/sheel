<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use App\Models\System\System;

class ProviderService extends Base
{
    public function createProvider($request)
    {
         // after validate all data filter data only 

         $user = User::create($request->except(['emairate_id_front','emairate_id_back','drive_photo','RTA_card_front','RTA_card_back','vehicle_registration_form']));
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

        $message =  ($this->lang == 'ar')? 'تم التسجيل بنجاح'  : "Register Complete Successfully";
        return success([],System::HTTP_OK,$message);
    }

    public function acceptProvider(Provider $provider)
    {
        // verify user mobile
        $user_id = $provider->user_id;
        $user = User::find($user_id);
        $user->verify('mobile');
        
        User::createOtp();
        
    }
}