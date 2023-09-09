<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestProvider extends Model
{
    protected static function booted() 
    {
        static::addGlobalScope(new ActiveScope);
    }
    
    protected $table = 'requests_providers';
    protected $fillable = [
        'provider_id',
        'request_id',
        'status',
        'removed'
    ];

    const STATUS_PENDING = 2;
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 0;
    const STATUS_CANCELED = 3;

    // status 
    // 2 pending 
    // 1 accept 
    // 0 refused
    // 3 canceled
    use HasFactory;
}
