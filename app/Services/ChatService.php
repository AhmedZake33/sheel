<?php

namespace App\Services;
use App\Events\ChatEvent;
use App\Models\System\System;
use Auth;

class ChatService extends Base {
    
    public function sendMessage($request , $requestModel , $ReceivingUser)
    {
        // create chat record
        $chat = Auth::user()->chats()->createMany([
           ["message" => $request->message , 'request_id' => $requestModel ,'received_id' => $ReceivingUser]
        ]);

        // fire broadcast
        ChatEvent::dispatch($request->message , $requestModel);


        if($chat){
            return success([] , System::HTTP_OK , 'success');
        }
        return false;
    }
}