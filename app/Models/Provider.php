<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['service_id','user_id','lat','lng'];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id','id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class , 'service_id','id');
    }


}
