<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\requestProvider;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\System;

class Provider extends Model
{
    protected $fillable = ['service_id','user_id','lat','lng'];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id','id')->select('id','name','email','mobile','mobile_code');
    }

    public function service()
    {
        return $this->belongsTo(Service::class , 'service_id','id');
    }

    public function requestsProviders($status = null)
    {
        $query =  $this->hasMany(requestProvider::class);
        if($status != null){
            $query->where("status" , $status);
        }
        return $query;
    }

    public function data($type = 0)
    {
        $data = (object)[];
        if($type = System::DATA_ORIGINAL){
            $data->id = $this->id;
            $data->service_id = $this->service_id;
            $data->lat = $this->lat;
            $data->lng = $this->lng;
            $data->user_id = $this->user_id;
            $data->user = $this->user->data(System::DATA_ORIGINAL);
        }

        return $data;
    }


}
