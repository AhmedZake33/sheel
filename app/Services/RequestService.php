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
        if($request->estimate){
            $locationProvider = new LocationService();
            $data = (object)[];
            $requestModel = new requestModel();
            $amount = $locationProvider->calcDistance($request->current_lat , $request->current_lng , $request->destination_lat , $request->destination_lng)*env('costPerKilo');
            // $amount = number_format($amount , 2);
            $payment = Payment::createAndUpdate(['amount' => $amount, 'user_id' => auth()->id() , 'promo_code_id' => $request->promo_code_id,'request_id' => $requestModel->id] , true);
            // $data->cost =  $locationProvider->calcDistance($request->current_lat , $request->current_lng , $request->destination_lat , $request->destination_lng)*env('costPerKilo');
            return success($payment , 200);
        }
        $user =  auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $requestModel = null;
        if($request->request_id){
            $requestModel = RequestModel::find($request->request_id);
            $requestModel->fill($data);
        }else{
            $requestModel = RequestModel::create($data);
        }

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
            $payment = Payment::createAndUpdate(['amount' => $amount, 'user_id' => $requestModel->user_id , 'promo_code_id' => $request->promo_code_id,'request_id' => $requestModel->id , "card_id" =>$request->card_id??null , "provider_id" => $request->provider_id??null]);
            
            $requestModel->payment_id = $payment->id;
            $requestModel->save();
        }
        // return $data;

        $requestModel->startFindProvider();

        // if(domain() != "http://127.0.0.1:8000" && $user->id == 6){
        //     $requestModel->autoAssignProvider(1);
        // }
        
        
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

    public function estimateCost($request)
    {
        return $request->estimate;
    }
    
    public function pay($request , $requestModel)
    {
        if($request->card_id){
            $card = Card::find($request->card_id);
            // return $card;
            if($card->user_id == auth()->id()){
                // return $card;
                $transaction = Transaction::find($request->transaction_id);
                // api to complete pay and update request ....
                $paymentService = new PaymentService();
                if(!$card->token){
                    $paymentService->createTokenFromCard($card->id);
                }     
                $card = $card->fresh();
                       
                return $paymentService->buy($transaction ,$card);
                
            }else{
                $message = (app()->getLocale() == 'en') ? 'something went wrong' : 'حدث خطأ ما';
            return success([],System::HHTP_Unprocessable_Content,$message);
            }
        }
    }
}