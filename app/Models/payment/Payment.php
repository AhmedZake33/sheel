<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_provider_id','amount','user_id','promo_code_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function request()
    {
        return $this->hasOne(Request::class);
    }

    public function createAndUpdate($data)
    {
        // $payment = new Payment();
        $this->amount = $data['amount'];
        $this->user_id = $data['user_id'];
        $this->promo_code_id = $data['promo_code_id'];
        $this->save();
        
        // send if valid
        if($data['promo_code_id']){
            $promocode = PromoCode::find($data['promo_code_id']);
            $discount = $promocode->discount;
            if($discount){
                $this->amount = $this->amount - $discount;
                $this->save();
            }
        }
        return $this;

    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
