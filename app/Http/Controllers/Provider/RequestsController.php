<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\requestCreateRequest;
use App\Models\System\System;
use App\Models\Request as RequestModel;
use App\Services\LocationService;
use App\Services\ProviderRequestService;
use App\Models\Provider;
use App\Models\RequestProvider;
use App\Models\Notification;

class RequestsController extends Controller
{
    protected $locationService = null;
    protected $providerRequestService = null;

    public function __construct(LocationService $locationService, ProviderRequestService $providerRequestService)
    {
        $this->locationService = $locationService;
        $this->providerRequestService = $providerRequestService;
    }

    public function cancel(Request $request)
    {
        // check if auth user is provider in this request 
        $requestModel = RequestModel::find($request->request_id);
        $user = auth()->user();
        $provider = Provider::where('user_id',$user->id)->first();
        if($provider){
            // get request provider 
            $requestProvider = RequestProvider::where('request_id',$requestModel->id)->where('provider_id',$provider->id)->where('status',RequestProvider::STATUS_PENDING)->first();
            if($requestProvider){
                
                $requestProvider->update(['status' => RequestProvider::STATUS_REFUSED]);

                // seen notification 
                $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                Notification::seen($notification);

                // get nearest locations
                $nearestLocations =  $this->locationService->getNearestLocations($requestModel , $user->id);
               
                // get nearest location 
                if($nearestLocations){
                    $nearestLocation = $this->locationService->getNearestLocation($requestModel->lat , $requestModel->lng , $nearestLocations);
        
                    // assign to provider 
                    if($nearestLocation){
                        $this->providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

                        // notification to new provider
                        $title = ['ar' => 'arabic' , 'en' => 'english'];
                        Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
                    }
                }
                return success([],System::HTTP_OK,'SUCCESS CANCEL REQUEST');
            }
        }
        return success([],System::HTTP_UNAUTHORIZED,'Not Authorized');
       
        
    }

    public function accept(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request->request_id);
        $user = auth()->user();
        $provider = Provider::where('user_id',$user->id)->first();
        // return $provider;
        if($provider){
            // get request provider 
            $requestProvider = RequestProvider::where('request_id',$requestModel->id)->where('provider_id',$provider->id)->where('status',RequestProvider::STATUS_PENDING)->first();
            if($requestProvider){
                $requestProvider->update(['status' => RequestProvider::STATUS_ACCEPTED]);

                // seen notification
                $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                if($notification){
                    Notification::seen($notification);
                }
                
                
                // notification to request user that provider is comming
                $title = ['ar' => 'provider is comming' , 'en' => 'provider is comming'];
                Notification::createNotification($requestModel->user_id , $requestModel->id , $title);
                    
            }else{
                return success([] ,System::HTTP_UNAUTHORIZED , 'Not Authorized');
            }

        }
        return success([],System::HTTP_OK,'SUCCESS Accept REQUEST');
    }

    public function show(Request $request)
    {
       $request = RequestModel::findOrFail($request->request_id);

        return success($request->data(),System::HTTP_OK,'SUCCESS');
    }
}
