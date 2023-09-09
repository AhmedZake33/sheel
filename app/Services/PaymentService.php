<?php

namespace App\Services;

use App\Models\Payment\Payment;

class PaymentService extends Base {

    public static function create($data)
    {
        // create payment and return it 
        $payment = Payment::create($data);
        return $payment;
        
    } 
}