<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archive;
use App\Models\System\System;
use App\Services\LocationService;
use App\Services\ProviderRequestService;
use App\Models\Payment\Payment;

class Request extends Model
{
    use HasFactory;
    
    protected $fillable = ['current_lat','current_lng','destination_lat','destination_lng','service_id','user_id','description','status'];

    // new => 0
    // accepted => 1
    // cancel => 2
    const STATUS_ACCEPTED  = 1;
    const STATUS_NEW  = 0;
    const STATUS_CANCEL = 2;

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

    public function provider()
    {
        return $this->hasMany(RequestProvider::class , 'request_id' , 'id')->where('requests_providers.status',1);
    }

    public function CurrentProvider()
    {
        return $this->hasOne(RequestProvider::class , 'request_id' , 'id')->where('requests_providers.status',1);
    }

    public function payment()
    {
        return $this->belongsto(Payment::class);
    }

    public function user()
    {
        return $this->belongsto(User::class)->select('id','name','email');
    }

    public function data($type = System::DATA_BRIEF)
    {
        $locationProvider = new LocationService();
        $data = (object)[];
        $data->id = $this->id;
        $data->user = $this->user;
        $data->current_latituide = $this->current_lat;
        $data->current_lngituide = $this->current_lng;
        $data->destination_latituide = $this->destination_lat;
        $data->destination_lngituide = $this->destination_lng;
        $data->payment = $this->payment;
        $data->service = $this->service;
        $files = $this->archive->children;
        $data->provider = $this->CurrentProvider?$this->CurrentProvider->provider->user : null;
        $data->chats = $this->chats;
        $data->distance = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng);
        $data->estimatedCost = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng)*env('costPerKilo');
        // $data->estimatedCost = 100;
        $temp_files = [];
        foreach($files as $file){
            array_push($temp_files , route('download_file',$file));
        }
        // $data->pay = $this->payment ? route('buy',[$this->id,'otfff']) : null;  
        $data->files = $temp_files;
        // $data->file = 
        if($type == System::DATA_BRIEF){

        }elseif($type == System::DATA_DETAILS){
            $data->created_at = $this->created_at;
            $data->updated_at = $this->updated_at; 
            
        }

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
        // return $requestModel->user->is(auth()->user());

        if(  ($requestModel->CurrentProvider  && $requestModel->CurrentProvider->provider->user->is($user)) || $requestModel->user->is($user)){
            return true;
        }

        if(!$requestModel->CurrentProvider){
            return false;
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

    public function startShowLocation()
    {
        // service to get nearest locations
        $locationService = new LocationService();
        $providerRequestService = new providerRequestService();
        $nearestLocations =  $locationService->getNearestLocations($this);
        // service to get nearest location  
        if($nearestLocations){
            $nearestLocation = $locationService->getNearestLocation($this->current_lat , $this->current_lng , $nearestLocations);
            // assign to provider 
            if($nearestLocation){
                $existProvider = RequestProvider::where('request_id',$this->id)->where('status',RequestProvider::STATUS_PENDING)->first();
                if(!$existProvider){
                    $providerRequestService->assignProvider($nearestLocation->user_id , $this->id);
                }
                // notification to provider
                $title = ['ar' => 'arabic' , 'en' => 'english'];
                Notification::createNotification($nearestLocation->user_id , $this->id , $title);
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
            $this->startShowLocation();
        }
    }
}
