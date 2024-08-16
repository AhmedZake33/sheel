<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archive;
use App\Models\System\System;
use App\Services\LocationService;
use App\Services\ProviderRequestService;
use App\Models\Payments\Payment;
use App\Services\StripeService;

class Request extends Model
{
    use HasFactory;
    
    protected $fillable = ['current_lat','current_lng','destination_lat','destination_lng','service_id','user_id','description','status',"cancel_reason"];

    // new => 0
    // accepted => 1
    // cancel => 2
    // complete = 3

    const STATUS_ACCEPTED  = 1;
    const STATUS_PENDING  = 0;
    const STATUS_CANCEL = 2;
    const STATUS_COMPLETE = 3;


    // IMAGE_ON_GROUND
    // IMAGE_IN_DESTINATION


    // pending 
    // accept / cancel 
    // time out 
    // complete

    public function decodeStatus($id)
    {
        $statuses = [
            ["id"=>self::STATUS_PENDING ,"name"=>"Pending","name_local"=>"قيد الانتظار"],
            ["id"=>self::STATUS_ACCEPTED ,"name"=>"Accepted","name_local"=>"مقبول"],
            ["id"=> self::STATUS_CANCEL ,"name"=>"Canceled","name_local"=>"ملغي"],
            ["id"=> self::STATUS_COMPLETE ,"name"=>"Completed","name_local"=>"مكتمل"]
        ];

        foreach($statuses as $status){
            if($status["id"] == $id){
                return $status;
            }
        }
    }

    public function archive()
    {
        if (empty($this->archive_id)) {
            $this->createArchive();
        }
        return $this->belongsTo(Archive::class, 'archive_id', 'id');
    }

    public function createArchive()
    {
        if($this->archive_id){
            return $this->archive_id;
        }
        $archive = Archive::getWithName("requests/$this->id", "($this->id)");
        $this->archive_id = $archive->id;
        $this->timestamps = false;
        $this->save();
        return $this->archive_id;
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->select('id','name_local','name');
    }

    public function providers()
    {
        return $this->hasMany(RequestProvider::class , 'request_id' , 'id')->where('requests_providers.status',1);
    }

    public function CurrentProvider()
    {
        return $this->hasOne(RequestProvider::class , 'request_id' , 'id')->where('requests_providers.status',1);
    }

    public function provider($provider_id = null)
    {
        $query =  $this->hasOne(RequestProvider::class , 'request_id' , 'id');
        if($provider_id){
            $query->where("provider_id",$provider_id);
        }

        return $query;
    }

    public function payment()
    {
        return $this->belongsto(Payment::class);
    }

    public function user()
    {
        return $this->belongsto(User::class)->select('id','name','email',"mobile","mobile_code");
    }

    public function data($type = System::DATA_BRIEF)
    {
        $locationProvider = new LocationService();
        $data = (object)[];
        $data->id = $this->id;
        $data->user = $this->user;
        $data->status = $this->decodeStatus($this->status);
        $data->current_latituide = (double)$this->current_lat;
        $data->current_lngituide = (double)$this->current_lng;
        $data->destination_latituide = (double)$this->destination_lat;
        $data->destination_lngituide = (double)$this->destination_lng;
        $data->payment = $this->payment;
        $data->discount = ($this->payment && $this->payment->promoCode)? $this->payment->promoCode : null;
        $data->service = $this->service;
        $files = $this->archive->children;
        $data->provider = $this->CurrentProvider? $this->CurrentProvider->provider->with("user")->first() : null;
        $data->canPay = $this->CurrentProvider? true : false;
        // $data->review = $this->review()->select('rate','comment')->first();
        $data->distance = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng);
        $data->estimatedCost = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng)*env('costPerKilo');
        // $data->estimatedCost = 100;
        $temp_files = [];
        foreach($files as $file){
            if(!in_array( $file->short_name, ["IMAGE_IN_DESTINATION","IMAGE_ON_GROUND"])){
                array_push($temp_files , route('download_file',$file));
            }
        }
        if($type == System::DATA_BRIEF){

        }elseif($type == System::DATA_DETAILS){
            $data->created_at = $this->created_at;
            $data->updated_at = $this->updated_at; 
            $data->chats = $this->chats;
        }
        $data->files = $temp_files;
        $data->allowDestinationButton = $this->ifProviderNearset();
        $data->isPickedUp = $this->isPickedUp();
        $data->isArrived = $this->isArrived();
        $data->userReview = $this->user->calculateReview();
        $data->providerReview = $this->CurrentProvider?$this->CurrentProvider->provider->user->calculateReview(): null;
        return $data;
    }

    public function ifProviderNearset()
    {
        $locationProvider = new LocationService();
        $currentProviderData =  $this->CurrentProvider?$this->CurrentProvider->provider : null;
        // return $currentProviderData;
        if($currentProviderData){
            $distanceInKilos =  $locationProvider->calcDistance($this->destination_lat , $this->destination_lng , $currentProviderData->lat , $currentProviderData->lng);
            // return $distanceInKilos * 1000;
            if($distanceInKilos * 1000 < 150){
                // pay requst
                try{
                    StripeService::pay($this->payment);
                }catch(\Exception $ex){
                    return $ex->getMessage();
                }
                
                return true;
                
            }
            // return $distanceInKilos * 1000;
            
        }
        return false;
        
    }

    public function providerData($provider = null)
    {
        $locationProvider = new LocationService();
        $data = (object)[];
        $data->id = $this->id;
        $data->user = $this->user->data(System::DATA_ORIGINAL);
        $data->status = $this->decodeStatus($this->status);
        $data->current_latituide = (double)$this->current_lat;
        $data->current_lngituide = (double)$this->current_lng;
        $data->destination_latituide = (double)$this->destination_lat;
        $data->destination_lngituide = (double)$this->destination_lng;
        $data->provider = $this->provider($provider->id) ? $this->provider($provider->id)->first()->data() : null;
        $data->payment = $this->payment;
        $data->discount = ($this->payment && $this->payment->promoCode)? $this->payment->promoCode : null;
        $data->service = $this->service;
        $files = $this->archive->children;
        $data->chats = $this->chats;
        $data->distance = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng);
        $data->estimatedCost = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng)*env('costPerKilo');
        // $data->estimatedCost = 100;
        $temp_files = [];
        foreach($files as $file){
            if(!in_array( $file->short_name, ["IMAGE_IN_DESTINATION","IMAGE_ON_GROUND"])){
                array_push($temp_files , route('download_file',$file));
            }
        }
        $data->files = $temp_files;        
        $data->created_at = $this->created_at;
        $data->updated_at = $this->updated_at; 
        $data->userReview = $this->user->calculateReview();
        return $data;   
    }
    
    public function isPickedUp()
    {
        $pickupArchive = $this->archive->children->where("short_name","IMAGE_ON_GROUND")->first();
        if($pickupArchive){
            return true;
        }   
        return false;
    }

    public function isArrived()
    {
        $pickupArchive = $this->archive->children->where("short_name","IMAGE_IN_DESTINATION")->first();
        if($pickupArchive){
            return true;
        }   
        return false;
    }

    public function notificationData()
    {
        $locationProvider = new LocationService();
        $data = (object)[];
        $data->id = $this->id;
        $data->user = $this->user->data(System::DATA_ORIGINAL);
        $data->provider = $this->CurrentProvider? $this->CurrentProvider->provider->data() : null;
        $data->current_latituide = (double)$this->current_lat;
        $data->current_lngituide = (double)$this->current_lng;
        $data->destination_latituide = (double)$this->destination_lat;
        $data->destination_lngituide = (double)$this->destination_lng;
        $files = $this->archive->children;
        $data->chats = $this->chats;
        $data->distance = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng);
        $data->estimatedCost = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng)*env('costPerKilo');
        $temp_files = [];
        foreach($files as $file){
            if(!in_array( $file->short_name, ["IMAGE_IN_DESTINATION","IMAGE_ON_GROUND"])){
                array_push($temp_files , route('download_file',$file));
            }
        }
        $data->files = $temp_files;
        $data->created_at = $this->created_at;
        $data->updated_at = $this->updated_at; 
        $data->userReview = $this->user->calculateReview();
        $data->providerReview = $this->CurrentProvider?$this->CurrentProvider->provider->user->calculateReview(): null;
        $data->allowDestinationButton = $this->ifProviderNearset();

        return $data;   
    }

    public function refusedProviders()
    {
        $users = RequestProvider::leftJoin('providers','providers.id','requests_providers.provider_id')->where('requests_providers.request_id',$this->id)
        ->where('requests_providers.status',0)->pluck('providers.user_id')->toArray();

        return $users;
    }

    public static function canAccess($requestModel,$user)
    {
        
        $requestModel = Request::find($requestModel);

        if(  ($requestModel->CurrentProvider  && $requestModel->CurrentProvider->provider->user->is($user)) || $requestModel->user->is($user)){
            return true;
        }

        if(!$requestModel->CurrentProvider){
            return false;
        }

        return false;
    }

    public static function isProvider($requestModel,$user)
    {
        
        $requestModel = Request::find($requestModel);
        if(!$requestModel->CurrentProvider){
            return false;
        }

        if(($requestModel->CurrentProvider  && $requestModel->CurrentProvider->provider->user->is($user))){
            return true;
        }

        return false;
    }

    public static function canReview($requestModel,$user)
    {
        
        $requestModel = Request::find($requestModel);

        if(!$requestModel->CurrentProvider){
            return false;
        }
        // return $requestModel->CurrentProvider->provider->user->is($user);
        if( ($requestModel->CurrentProvider)  && (($requestModel->user->is($user)) || ($requestModel->CurrentProvider->provider->user->is($user)))  ){
            return true;
        }

        return false;
    }

    public function chats()
    {
        return $this->hasMany(Chat::class)->orderBy('created_at','DESC');
    }

    public function getReceivingUser()
    {
        if(auth()->id() == $this->user_id){
            return $this->CurrentProvider->provider->user->id;
        }else if(auth()->id() == $this->CurrentProvider->provider->user->id){
            return $this->user_id;
        }else{
            return;
        }
    }

    public function startFindProvider()
    {
        // service to get nearest locations
        $locationService = new LocationService();
        $providerRequestService = new providerRequestService();
        $nearestLocations =  $locationService->getNearestLocations($this , $this->refusedProviders());

        // service to get nearest location  
        if(count($nearestLocations)){
            $nearestLocation = $locationService->getNearestLocation($this->current_lat , $this->current_lng , $nearestLocations);
        // return $nearestLocation;
            // assign to provider 
            if($nearestLocation){
                $existProvider = RequestProvider::where('request_id',$this->id)->where('status',RequestProvider::STATUS_PENDING)->first();
                if(!$existProvider){
                    $providerRequestService->assignProvider($nearestLocation->user_id , $this->id);
                    // event(new \App\Events\CurrentRequests($nearestLocation->user_id , $this));
                }
                // notification to provider
                // $title = ['ar' => 'لقد تم اضافتك الي طلب' , 'en' => 'you have assigned to request'];
                // Notification::createNotification($nearestLocation->user_id , $this->id , $title);
            }
            
        }
    }

    public function manualPay()
    {
        $payment = Payment::where('id',$this->payment_id)->first();
        if($payment){
            $payment->status = 1;
            $payment->paid = $payment->amount;
            $payment->save();
        }

        if(!$this->CurrentProvider()){
            $this->startFindProvider();
        }
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function autoAssignProvider($id)
    {
        RequestProvider::create(['provider_id'  => $id, 'request_id' => $this->id , 'status' => RequestProvider::STATUS_PENDING]);
        event(new \App\Events\CurrentRequests(4 , $this));
    }
}
