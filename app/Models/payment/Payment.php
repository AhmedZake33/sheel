<?php

namespace App\Models\payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_provider_id','amount','user_id','promo_code_id'];

    public function user()
    {
        return $this->belongsTo(User::Class);
    }
    
    public function request()
    {
        return $this->hasOne(Request::class);
    }

    public function createAndUpdate($data)
    {
        $payment = new Payment();
        $payment->payment_provider_id = $data['payment_provider_id'];
        $payment->amount = $data['amount'];
        $payment->user_id = $data['user_id'];
        $payment->promo_code_id = $data['promo_code_id'];
        $payment->save();
        
        // send if valid
        if($data['promo_code_id']){
            $promocode = PromoCode::find($data['promo_code_id']);
            $discount = $promocode->discount;
            if($discount){
                $payment->amount = $payment->amount - $discount;
                $payment->save();
            }
        }
        return $payment;

    }
}
