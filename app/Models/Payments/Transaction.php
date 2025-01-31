<?php

namespace App\Models\Payments;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

    protected $casts = [
        'amount' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

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
            $transaction->data = $result;
            $transaction->paid = $result['amount'];
            $payment = $transaction->payment;
            if($transaction->paid >= $transaction->amount){
                $transaction->status = 1;
            }
            $transaction->save();

            if($transaction->status == 1){
                $payment->status = 1;
                $payment->paid =  self::where('payment_id', $this->payment->id)->where('status', 1)->sum('paid');
                $payment->card_id = null;
                $payment->save();
            }
        }
    }
}
