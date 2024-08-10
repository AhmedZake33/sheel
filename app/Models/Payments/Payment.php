<?php

namespace App\Models\Payments;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $casts = [
        'amount' => 'decimal:2',
        'paid' => 'decimal:2',
    ];
    
    protected $hidden = ['promoCode'];

    protected $fillable = ['payment_provider_id','amount','user_id','promo_code_id','card_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function request()
    {
        return $this->hasOne(Request::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public static function createAndUpdate($data , $estimate = false)
    {
        $carbon  = Carbon::now();
        return $carbon->toDateTimeString();
        $payment = new Payment();
        $payment->amount = number_format($data['amount'] , 3);
        $payment->user_id = $data['user_id'];
        $payment->promo_code_id = $data['promo_code_id'];
        $payment->card_id = $data['card_id']??null;
        // $payment->save();
        // return $payment;
        // send if valid
        if($data['promo_code_id']){
            $promocode = PromoCode::find($data['promo_code_id']);
            $discount = $promocode->discount;
            if($discount){
                $payment->amount = $payment->amount - $discount;
                // $payment->save();
            }
        }
        // return $payment;
        if($estimate){
            return $payment;
        }
        $payment->save();

        // create transaction 
        if($data["provider_id"]){
            $transaction = new Transaction();
            $transaction->payment_id = $payment->id;
            $transaction->amount = number_format($data['amount'] , 3);
            $transaction->provider_id =$data['provider_id']??null;;
            $transaction->save();
        }   
        return $payment;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
