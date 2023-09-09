<?php

namespace App\Models\payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProviders extends Model
{
    protected $table = 'payment_providers';
    use HasFactory;
}
