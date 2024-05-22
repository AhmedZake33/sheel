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
use DB;
use App\Models\User;

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
                // $notification = Notification::where(['user_id' => auth()->user()->id , 'request_id' => $requestModel->id , 'seen' => Notification::UNSEEN])->first();
                // Notification::seen($notification);

                // get nearest locations
                // $nearestLocations =  $this->locationService->getNearestLocations($requestModel , $user->id);
               
                // // get nearest location 
                // if($nearestLocations){
                //     $nearestLocation = $this->locationService->getNearestLocation($requestModel->lat , $requestModel->lng , $nearestLocations);
        
                //     // assign to provider 
                //     if($nearestLocation){
                //         $this->providerRequestService->assignProvider($nearestLocation->user_id , $requestModel->id);

                //         // notification to new provider
                //         $title = ['ar' => 'arabic' , 'en' => 'english'];
                //         Notification::createNotification($nearestLocation->user_id , $requestModel->id , $title);
                //     }
                // }
                $requestModel->startFindProvider();
                $title = ['ar' => 'provider refuse request' , 'en' => 'provider refuse request'];
                Notification::createNotification($requestModel->user_id , $requestModel->id , $title);
                return success(["refused" => true],System::HTTP_OK,'SUCCESS CANCEL REQUEST');
            }
        }
        return success([],System::HTTP_UNAUTHORIZED,'Not Authorized');        
    }

    public function accept(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request->request_id);
        $user = auth()->user();
        $provider = Provider::where('user_id',$user->id)->first();
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

                return success(["accepted" => true],System::HTTP_OK,'SUCCESS Accept REQUEST');
                    
            }else{
                return success([] ,System::HTTP_UNAUTHORIZED , 'Not Authorized');
            }

        }
        return success([],System::HHTP_Unprocessable_Content,'error');
    }

    public function show(Request $request)
    {
       $request = RequestModel::findOrFail($request->request_id);

        return success($request->data(),System::HTTP_OK,'SUCCESS');
    }

    public function current(Request $request)
    {
        // get all request that are pending to user 
        $user = auth()->user();
        $provider =  $user->provider;

        // update provider
        $provider->lat = $request->lat;
        $provider->lng = $request->lng;
        $provider->save();

        // return $provider;
        // request provider
        $requestsProvider = DB::table("requests_providers")->where("provider_id",$provider->id)->where("status",2)->select("request_id")->get();
        $result = [];
        if(count($requestsProvider)){
            foreach($requestsProvider as $requestProvider){
                // get request
                $request = RequestModel::find($requestProvider->request_id);
                if($request){
                    array_push($result , $request->data());
                }
            }
            return success($result,System::HTTP_OK , 'success');
        }
        return success(System::HTTP_OK , 'success');
    }

    public function history()
    {
        $user = User::find(auth()->id());
        $provider = $user->provider;
        // return $user;
        // $requests = RequestModel::join("requests_providers","requests_providers.request_id","requests.id")
        // ->join("providers","providers.id","requests_providers.provider_id")
        // ->join("users","users.id","providers.user_id")
        // ->select("requests.*")
        // ->orderBy("requests.id","DESC")
        // ->where("providers.user_id",$user->id)
        // ->get()->transform(function($request){
        //     return $request->data();
        // });

        $requests = RequestModel::join("requests_providers","requests_providers.request_id","requests.id")
        ->join("providers","providers.id","requests_providers.provider_id")
        ->join("users","users.id","providers.user_id")
        ->select("requests.*","requests_providers.id as requests_providers_id")
        ->orderBy("requests.id","DESC")
        ->where("providers.user_id",$user->id)
        ->with(["provider" => function($query) use ($provider){
            $query->where("provider_id",$provider->id);
        } , "payment"])
        ->get()->transform(function($data) use($provider){
            // $data->provider = $data->provider->data();
            return $data->providerData($provider);
        });
        return success(["history" =>$requests],System::HTTP_OK,'SUCCESS');
    }

    public function pickUp(Request $request , $id)
    {
        //IMAGE_ON_GROUND
        $requestData = requestModel::findOrFail($id);
        if(RequestModel::isProvider($requestData->id , auth()->user())){
            // upload image
            if($request->file && !$requestData->archive->findChildByShortName("IMAGE_ON_GROUND")){
                $requestData->archive->addDocumentWithShortName($request->file , "IMAGE_ON_GROUND","IMAGE_ON_GROUND","IMAGE_ON_GROUND");
                // action to start request

                // fire event 
                event(new \App\Events\CurrentRequests($requestData->user_id , $requestData));
                return success([],System::HTTP_OK , "SUCCESS");
            }else{
                return $requestData->archive->findChildByShortName("IMAGE_ON_GROUND");
                return success([],System::HHTP_Unprocessable_Content , "IMAGE UPLOADED BEFORE");
            }
        }
        return success([],System::HHTP_Unprocessable_Content , "CANNOT UPLOAD IMAGE");
    }

    public function arrived(Request $request , $id)
    {
        //IMAGE_IN_DESTINATION
        $requestData = requestModel::findOrFail($id);
        if(RequestModel::isProvider($requestData->id , auth()->user())){
            // upload image
            if($request->file && $requestData->ifProviderNearset() && !$requestData->archive->findChildByShortName("IMAGE_IN_DESTINATION")){
                $requestData->archive->addDocumentWithShortName($request->file , "IMAGE_IN_DESTINATION","IMAGE_IN_DESTINATION","IMAGE_IN_DESTINATION");
                // action to start request

                // update request and update request provider
                $requestData->update(["status" => RequestModel::STATUS_COMPLETE]);

                $requestProvider=  RequestProvider::where(["request_id" => $requestData->id , "provider_id" => auth()->user()->provider->id])->first();
                if($requestProvider){
                    $requestProvider->update(["status" => RequestProvider::STATUS_COMPLETE]);
                }

                // fire event 
                event(new \App\Events\CurrentRequests($requestData->user_id , $requestData));
                
                return success([],System::HTTP_OK , "SUCCESS");
            }else{
                return success([],System::HHTP_Unprocessable_Content , "IMAGE UPLOADED BEFORE");
            }
        }
        return success([],System::HHTP_Unprocessable_Content , "CANNOT UPLOAD IMAGE");
    }
}
