<?php

namespace App\Services;

use App\Models\Request as RequestModel;
use App\Services\LocationService;
use App\Models\Notification;
use App\Models\System\System;
use App\Models\Payment\Payment;

class RequestService extends Base 
{
    public static function create($request)
    {

        $user =  auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $requestModel = RequestModel::create($data);

        if(count($data['file']) > 0){
            // create archive 
            foreach($data['file'] as $file){
                $requestModel->archive->addFile($file);
            }
            
        }

        // create payment 
        $payment = new Payment();
        $payment = $payment->createAndUpdate(['payment_provider_id' => $request->payment_provider_id , 'amount' => 100, 'user_id' => $requestModel->user_id , 'promo_code_id' => $request->promo_code_id,'request_id' => $requestModel->id]);
        $requestModel->payment_id = $payment->id;
        $requestModel->save();
        
        // service to get nearest locations
        $locationService = new LocationService();
        $providerRequestService = new providerRequestService();
        $nearestLocations =  $locationService->getNearestLocations($requestModel);
        // service to get nearest location  
        if($nearestLocations){
            $nearestLocation = $locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);

            // assign to provider 
            if($nearestLocation){
                $providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

                // notification to provider
                $title = ['ar' => 'arabic' , 'en' => 'english'];
                Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
            }
            
        }
        

        // now i have request : 
        return success($requestModel->data(),System::HTTP_OK,'SUCCESS CREATE REQUEST');
    }    
}