<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Models\Payment\Card;
use Illuminate\Http\Request;
use App\Models\System\System;
use App\Services\TapService;
use Auth;
class CardsController extends Controller
{
    protected  $paymentService = null;

    public function __construct(TapService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function addCard(CardRequest $request)
    {
        $user = auth()->user();
        $response = $this->paymentService->createToken($request);
        // return $response;
        if($response){
            $card = new Card();
            $card->user_id = 1;
            $card->token =$response['id'];
            // $card->card_id =$response['card']['id'];
            // $card->customer_id =$response['card']['customer'];
            $card->last_four = $response['card']['last_four'];
            $card->save();
            $message  = ($this->lang == 'ar')?  'تم إضافة البطاقة بنجاح' : 'Card added Successfully';
            return success([] , System::HTTP_OK ,$message);
        }
        
        $message  = ($this->lang == 'ar')?  'حدث خطأ' : 'SOMETHING WENT WRONG';
        return success([] , System::HHTP_Unprocessable_Content ,$message);
       
        
    }

    public function editCard(CardRequest $request , Card $card)
    {
        return $card;
    }
}
