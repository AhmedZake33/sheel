<?php

namespace App\Models\payment;

use App\Models\Payment\PromoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProviders extends Model
{
    protected $table = 'payment_providers';
    use HasFactory;

    public function promocode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
