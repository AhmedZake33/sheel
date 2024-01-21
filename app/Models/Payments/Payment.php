<?php

namespace App\Models\Payments;

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

    public static function createAndUpdate($data)
    {
        $payment = new Payment();
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
