<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Facades\Broadcast;
use App\Events\NotificationEvent;

class Notification extends Model
{
    protected $table = "notifications";
    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);
    }

    protected $fillable = ['user_id','request_id','title','seen','removed'];
    use HasFactory;

    const SEEN = 0;
    const UNSEEN = 1;

    public static function createNotification($user_id , $request_id , $title)
    {
        // create notification 
        // dd (gettype($title));

        $notification = Notification::create(['user_id' => $user_id , 'request_id' => $request_id]);
        $notification->title = $title;
        $notification->save();

        // fire event 
        NotificationEvent::dispatch($notification);
    }

    public static function seen($notification)
    {
        $user = auth()->user();
        if($notification->user_id == $user->id){
            $notification->update(['seen' => Notification::SEEN]);
        }
        return true;
    }
}
