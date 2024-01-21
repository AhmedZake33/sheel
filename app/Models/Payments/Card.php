<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    protected $fillable = ['cvv','expiration','name','card_number','region'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
