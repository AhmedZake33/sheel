<?php

namespace App\Services;

use App\Models\Request as RequestModel;
use App\Services\LocationService;
use App\Models\Notification;
use App\Models\System\System;
use App\Models\Payments\Payment;
use App\Models\Payments\Card;
use App\Models\Payments\Transaction;
use App\Services\PaymentService;

class RequestService extends Base 
{
    public static function create($request)
    {
        $user =  auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $requestModel = null;
        if($request->request_id){
            $requestModel = RequestModel::find($request->request_id);
            $requestModel->fill($data);
            // return response()->json('update');
        }else{
            $requestModel = RequestModel::create($data);
        }
        // return $request->all();
        if($request->file && count($data['file']) > 0){
            // create archive 
            foreach($data['file'] as $file){
                $requestModel->archive->addFile($file);
            }
            
        }
        // return $requestModel->payment;
        if($request->with_payment && $requestModel->payment_id == null){
            // create payment 
            // $payment = new Payment();
            $locationProvider = new LocationService();
            $amount = $locationProvider->calcDistance($request->current_lat , $request->current_lng , $request->destination_lat , $request->destination_lng)*env('costPerKilo');
            $payment = Payment::createAndUpdate(['amount' => $amount, 'user_id' => $requestModel->user_id , 'promo_code_id' => $request->promo_code_id,'request_id' => $requestModel->id]);
            $requestModel->payment_id = $payment->id;
            $requestModel->save();
        }

        $requestModel->startFindProvider();

        if(domain() != "http://127.0.0.1:8000"){
            $requestModel->autoAssignProvider(5);
        }
        
        
        // if($request->card_id){
        //     $card = Card::find($request->card_id);
        //     if($card->user_id == auth()->id()){
        //         $transaction = Transaction::find($request->transaction_id);
        //         // api to complete pay and update request ....
        //         $paymentService = new PaymentService();
        //         $paymentService->buy($transaction ,$card->id);
        //         // service to get nearest locations
        //         // $locationService = new LocationService();
        //         // $providerRequestService = new providerRequestService();
        //         // $nearestLocations =  $locationService->getNearestLocations($requestModel);
        //         // // service to get nearest location  
        //         // if($nearestLocations){
        //         //     $nearestLocation = $locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);
        //         //     // assign to provider 
        //         //     if($nearestLocation){
        //         //         $providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

        //         //         // notification to provider
        //         //         $title = ['ar' => 'arabic' , 'en' => 'english'];
        //         //         Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
        //         //     }
                    
        //         // }
        //     }
        // }
        
        // now i have request : 
        if($request->payment){
            return success($requestModel->data(System::DATA_DETAILS),System::HTTP_OK,'SUCCESS CREATE REQUEST');
        }else{
            return success($requestModel->data(),System::HTTP_OK,'SUCCESS CREATE REQUEST');
        }
    } 
    
    public function pay($request , $requestModel)
    {
        if($request->card_id){
            $card = Card::find($request->card_id);
            if($card->user_id == auth()->id()){
                $transaction = Transaction::find($request->transaction_id);
                // api to complete pay and update request ....
                $paymentService = new PaymentService();
                if(!$card->token){
                    $paymentService->createTokenFromCard($card->id);
                }     
                $card = $card->fresh();
                // return $card->token;           
                return $paymentService->buy($transaction ,$card);
                // service to get nearest locations
                // $locationService = new LocationService();
                // $providerRequestService = new providerRequestService();
                // $nearestLocations =  $locationService->getNearestLocations($requestModel);
                // // service to get nearest location  
                // if($nearestLocations){
                //     $nearestLocation = $locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);
                //     // assign to provider 
                //     if($nearestLocation){
                //         $providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

                //         // notification to provider
                //         $title = ['ar' => 'arabic' , 'en' => 'english'];
                //         Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
                //     }
                    
                // }
            }else{
                $message = (app()->getLocale() == 'en') ? 'something went wrong' : 'حدث خطأ ما';
            return success([],System::HHTP_Unprocessable_Content,$message);
            }
        }
    }
}