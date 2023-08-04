<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestProvider extends Model
{
    protected $table = 'requests_providers';
    protected $fillable = [
        'provider_id',
        'request_id',
        'status'
    ];

    const STATUS_PENDING = 2;
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 0;

    // status 
    // 2 pending 
    // 1 accept 
    // 0 refused
    use HasFactory;
}
