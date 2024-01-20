<?php

namespace App\Services;
use App\Events\ChatEvent;
use App\Models\Request;
use App\Models\System\System;
use Auth;

class ChatService extends Base {
    
    public function sendMessage($request , $requestModel , $ReceivingUser)
    {
        // create chat record
        $chat = Auth::user()->chats()->createMany([
           ["message" => $request->message , 'request_id' => $requestModel ,'received_id' => $ReceivingUser]
        ]);
        // return $chat;
        // $user = \App\Models\User::find(2);

        // return Request::find(133)->CurrentProvider->provider->user->is($user);
        // return Request::canAccess($requestModel,$user);
        // fire broadcast
        ChatEvent::dispatch($chat[0]);


        if($chat){
            return success([] , System::HTTP_OK , 'success');
        }
        return false;
    }
}