<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast\Object_;

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

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function decodeStatus($id)
    {
        $statuses = [["id"=>0 ,"name"=>"Refused","name_local"=>"مرفوض"],["id"=>1 ,"name"=>"Accepted","name_local"=>"مقبول"],["id"=>2 ,"name"=>"Pending","name_local"=>"قيد الانتظار"]];
        foreach($statuses as $status){
            if($status["id"] == $id){
                return $status;
            }
        }
    }

    public function data()
    {
        $data = (Object)[];
        $data->data = $this->provider ? $this->provider->user : null;
        $data->status = $this->decodeStatus($this->status);
        return $data;
    }
}
