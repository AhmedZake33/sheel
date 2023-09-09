<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archive;
use App\Models\System\System;
use App\Services\LocationService;
use App\Models\payment\Payment;

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

    public function payment()
    {
        return $this->belongsto(Payment::class);
    }

    public function data($type = System::DATA_BRIEF)
    {
        $locationProvider = new LocationService();
        $data = (object)[];
        $data->id = $this->id;
        $data->current_latituide = $this->current_lat;
        $data->current_lngituide = $this->current_lng;
        $data->destination_latituide = $this->destination_lat;
        $data->destination_lngituide = $this->destination_lng;
        $data->service = $this->service;
        $files = $this->archive->children;
        $data->provider = $this->provider;
        $data->distance = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng);
        $data->estimatedCost = $locationProvider->calcDistance($this->current_lat , $this->current_lng , $this->destination_lat , $this->destination_lng)*env('costPerKilo');
        $temp_files = [];
        foreach($files as $file){
            array_push($temp_files , route('download_file',$file));
        }
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
}
