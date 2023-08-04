<?php

namespace App\Http\Controllers\Api;

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

    public function create(requestCreateRequest $request)
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

        // service to get nearest locations
        $nearestLocations =  $this->locationService->getNearestLocations($requestModel);
        // service to get nearest location  
        if($nearestLocations){
            $nearestLocation = $this->locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);

            // assign to provider 
            if($nearestLocation){
                $this->providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

                // notification to provider
                $title = ['ar' => 'arabic' , 'en' => 'english'];
                Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
            }
            
        }
        

        // now i have request : 
        return success($requestModel->data(),System::HTTP_OK,'SUCCESS CREATE REQUEST');
    }

    public function nearestLocations(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request['request']);
        return $this->locationService->getNearestLocations($requestModel);
        $data = (object)[];
        $user = (object)[];
        $user->id = 2;
        $user->name = 'zaki';
        $data->id = 1;
        $data->lat = '123';
        $data->lng = '123';
        $data->user = $user;
        $arr = [$data,$data,$data];
        
        return success($arr,System::HTTP_OK,'SUCCESS');
    }

    public function nearestLocation(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request['request']);
        // first get nearest locations
        $nearestLocations = $this->locationService->getNearestLocations($requestModel);
        // get lowest distance 
        $nearestLocation = $this->locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);
        // assign rquest to provider 
        // $this->providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

        // notification to provider 

        return success($nearestLocation , System::HTTP_OK,'SUCCESS');
        // $data = (object)[];
        // $user = (object)[];
        // $user->id = 2;
        // $user->name = 'zaki';

        // $data->id = 1;
        // $data->lat = '123';
        // $data->lng = '123';
        // $data->user = $user;

        // return success($data,System::HTTP_OK,'SUCCESS');
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
        $requestModel = RequestModel::find($request->request_id);
        $user = auth()->user();
        $provider = Provider::where('user_id',$user->id)->first();
        if($provider){
            // get request provider 
            $requestProvider = RequestProvider::where('request_id',$requestModel->id)->where('provider_id',$provider->id)->where('status',RequestProvider::STATUS_PENDING)->first();
            if($requestProvider){
                $requestProvider->update(['status' => RequestProvider::STATUS_ACCEPTED]);

                // seen notification
                $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                Notification::seen($notification);
                
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
