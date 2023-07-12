<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archive;
use App\Models\System\System;

class Request extends Model
{
    use HasFactory;

    protected $fillable = ['lat','lng','service_id'];

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

    public function data($type = System::DATA_BRIEF)
    {
        $data = (object)[];
        $data->id = $this->id;
        $data->latituide = $this->lat;
        $data->lngituide = $this->lng;
        $data->service = $this->service;
        if($type == System::DATA_BRIEF){

        }elseif($type == System::DATA_DETAILS){
            $data->created_at = $this->created_at;
            $data->updated_at = $this->updated_at;   
        }

        return $data;
    }
}
