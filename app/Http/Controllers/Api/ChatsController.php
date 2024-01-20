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
        if(!RequestModel::canAccess($requestModel,auth()->user())){
            return error(null,System::HTTP_UNAUTHORIZED);
        }
        
        $ReceivingUser = RequestModel::find($requestModel)->getReceivingUser();
        // return $ReceivingUser;
        return $this->ChatService->sendMessage($request , $requestModel , $ReceivingUser);
    }

    public function get(Request $request , $requestModel){
        $limit = $request->limit ?  $request->limit : 10;
        $requestModel = RequestModel::where('requests.id',$requestModel)
        ->join('chats','chats.request_id','requests.id')->select('chats.*')
        ->orderBy('chats.created_at','DESC')->limit($limit);
        return success($requestModel->get() , System::HTTP_OK , 'success');
    }
}
