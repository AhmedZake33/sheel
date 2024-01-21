<?php

namespace App\Models\Payments;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(){
        return $this->payment->user();
    }

    public function updateStatus($result) {
        $transaction = Transaction::find($this->id);
        if($transaction){
            $transaction->status = 1;
            $transaction->data =json_encode(fetchTransaction($result));
            $transaction->paid = $result->amount;
            $transaction->save();
            $payment = $transaction->payment;
            if($transaction->paid >= $transaction->amount){
                $payment->status = 1;
                $payment->paid =  self::where('payment_id', $this->payment->id)->where('status', 1)->sum('paid');
                $payment->save();

                // start show near By locations to Providers 
                $request = Request::where('payment_id',$payment->id)->firstOrFail();
                $request->startShowLocation();
            }
        }
    }
}
