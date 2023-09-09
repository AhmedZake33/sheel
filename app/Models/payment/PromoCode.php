<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\System;

class PromoCode extends Model
{
    use HasFactory;

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function data($type = null){
        if($type == System::DATA_BRIEF){
            $data = (object)[];
            $data->id = $this->id;
            $data->discount = $this->discount;
            $data->description = $this->description;
            return $data;
        }
    }
}
