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
use DB;
use App\Services\RequestService;


class RequestsController extends Controller
{
    protected $locationService = null;
    protected $providerRequestService = null;
    protected $requestService = null;

    public function __construct(LocationService $locationService, ProviderRequestService $providerRequestService , RequestService $requestService)
    {
        $this->locationService = $locationService;
        $this->providerRequestService = $providerRequestService;
        $this->requestService = $requestService;
    }

    public function create(requestCreateRequest $request)
    {
        // return $this->locationService->getNearestLocations($request);
        // if(count($this->locationService->getNearestLocations($request)) == 0){
        //     $message = ["ar" => "لا يمكنك عمل طلب الان" , "en" => "cannot create request now"];
        //     return error([],System::HHTP_Unprocessable_Content , $message[$this->lang]);
        // }
        return $this->requestService->create($request);
        // $user =  auth()->user();
        // $data = $request->validated();
        // $data['user_id'] = $user->id;
        // $requestModel = RequestModel::create($data);

        // if(count($data['file']) > 0){
        //     // create archive 
        //     foreach($data['file'] as $file){
        //         $requestModel->archive->addFile($file);
        //     }
            
        // }

        // // service to get nearest locations
        // $nearestLocations =  $this->locationService->getNearestLocations($requestModel);
        // // service to get nearest location  
        // if($nearestLocations){
        //     $nearestLocation = $this->locationService->getNearestLocation($requestModel->current_lat , $requestModel->current_lng , $nearestLocations);

        //     // assign to provider 
        //     if($nearestLocation){
        //         $this->providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

        //         // notification to provider
        //         $title = ['ar' => 'arabic' , 'en' => 'english'];
        //         Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
        //     }
            
        // }
        

        // // now i have request : 
        // return success($requestModel->data(),System::HTTP_OK,'SUCCESS CREATE REQUEST');
    }

    public function nearestLocations(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request['request']);
        $locations =  $this->locationService->getNearestLocations($requestModel);
        return success($locations , System::HTTP_OK,'SUCCESS');
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
    }

    public function cancel(Request $request)
    {
        // return $request->all();
        // check if auth user is provider in this request 
        $requestModel = RequestModel::find($request->request_id);
        $user = auth()->user();
        if($requestModel->user_id == $user->id){
            // user can cancel his request 
            $requestModel->update(['status' => RequestModel::STATUS_CANCEL,"cancel_reason" => $request->cancel_reason]);
            
            if($requestModel->CurrentProvider && $requestModel->CurrentProvider->provider->user->id){
                $title = ['ar' => 'User Cancel Request' , 'en' => 'User Cancel Request'];
                // Notification::createNotification($requestModel->CurrentProvider->provider->user->id , $requestModel->id , $title);
            }

            DB::table('requests_providers')->where('request_id',$requestModel->id)->update(['status' => RequestProvider::STATUS_CANCELED]);
            // DB::table('notifications')->where('request_id',$requestModel->id)->update(['removed' => 1]);

            $requestModel = $requestModel->fresh();
            
            return success(["isCanceled" => ($requestModel && $requestModel->status == 2)? true : false],System::HTTP_OK,'SUCCESS CANCEL Your Request');
        }
        $provider = Provider::where('user_id',$user->id)->first();
        if($provider && $requestModel->status == RequestModel::STATUS_NEW){
            // get request provider 
            $requestProvider = RequestProvider::where('request_id',$requestModel->id)->where('provider_id',$provider->id)->where('status',RequestProvider::STATUS_PENDING)->first();
            if($requestProvider){
                $requestProvider->update(['status' => RequestProvider::STATUS_REFUSED]);
                $refusedProviders = $requestModel->refusedProviders();
                if(!array_key_exists(auth()->user()->id , $refusedProviders)){
                    array_push($refusedProviders , auth()->user()->id);
                }
                // seen notification 
                $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                Notification::seen($notification);

                // get nearest locations
                $nearestLocations =  $this->locationService->getNearestLocations($requestModel , $refusedProviders);
               
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
                return success([],System::HTTP_OK,'SUCCESS CANCEL Request');
            }
        }
        return success([],System::HTTP_UNAUTHORIZED,'Not Authorized');
       
        
    }

    public function accept(Request $request)
    {
        $requestModel = RequestModel::where('id',$request->request_id)->where('status',RequestModel::STATUS_NEW)->first();
        $user = auth()->user();
        $provider = Provider::where('user_id',$user->id)->first();
        if($provider){
            // get request provider 
            $requestProvider = RequestProvider::where('request_id',$requestModel->id)->where('provider_id',$provider->id)->where('status',RequestProvider::STATUS_PENDING)->first();
            if($requestProvider){
                $requestProvider->update(['status' => RequestProvider::STATUS_ACCEPTED]);

                // update request 
                $requestModel->update(['status' => RequestModel::STATUS_ACCEPTED]);                
                // seen notification
                $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                Notification::seen($notification);
                
                // notification to request user that provider is comming
                
                $title = ['ar' => 'provider is Accepting Requesting' , 'en' => 'provider is Accepting Requesting'];
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
        //    return $request;
        if(!RequestModel::canAccess($request->id , auth()->user())){
            return error(null,System::HTTP_UNAUTHORIZED);
        }
        return success($request->data(),System::HTTP_OK,'SUCCESS');
    }

    public function pay(Request $request , $requestModel)
    {
        $requestModel = RequestModel::findOrFail($requestModel);
        if(!($requestModel && $requestModel->currentProvider)){
            return error([],System::HHTP_Unprocessable_Content);
        }
        if(count(auth()->user()->cards)){
            auth()->user()->cards()->update(['token' => null]);
        }
        return $this->requestService->pay($request , $requestModel->id);
    }

    public function manualPay(Request $request , $requestModel)
    {
        $request = RequestModel::findOrFail($requestModel);
        $request->manualPay();
        return success([],System::HTTP_OK,'SUCCESS');
    }
}
