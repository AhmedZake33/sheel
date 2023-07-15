<?php
namespace App\Services;
use App\Models\Provider;
use App\Models\User;
use App\Models\RequestProvider;


class ProviderRequestService 
{
   public static function assignProvider($user_id , $request_id , $previous = null)
   {
        // check for provider and request 
        $provider = Provider::where('user_id',$user_id)->firstOrFail();
        if($user_id && $request_id){
            // create new record in requests_providers table 
            RequestProvider::create(['provider_id'  => $provider->id , 'request_id' => $request_id]);

        }
   }
}

?>