<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::select('*')->count();
        // $title = (array)json_decode($notification->title);
        return response()->json(($notifications));
    }

    public function seen(Request $request)
    {
        $user = auth()->user();
        $notification = Notification::find($request->notification_id);
    }
}
