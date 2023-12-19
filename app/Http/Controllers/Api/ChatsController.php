<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\System;
use App\Services\ChatService;
use App\Models\Request as RequestModel;

class ChatsController extends Controller
{
    private $ChatService;
    public function __construct(ChatService $ChatService)
    {
        $this->ChatService = $ChatService;
    }
    public function send(Request $request , $requestModel)
    {   
        if(!RequestModel::canAccessChat($requestModel,auth()->user())){
            return error(null,System::HTTP_UNAUTHORIZED);
        }
        
        $ReceivingUser = RequestModel::find($requestModel)->getReceivingUser();
        // return getRecivingUser
        return $this->ChatService->sendMessage($request , $requestModel , $ReceivingUser);
    }
}
