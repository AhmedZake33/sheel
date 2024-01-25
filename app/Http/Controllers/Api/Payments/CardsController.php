<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Models\Payments\Card;
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

    public function cards()
    {
        $user =  auth()->user();
        // return $user;
        return success($user->cards(true) , System::HTTP_OK , 'success');
    }

    public function addCard(CardRequest $request)
    {
        $user = auth()->user();
        $response = $this->paymentService->createToken($request);
        // return $response;
        if($response){
            $card = new Card();
            $card->user_id = $user->id;
            $card->token =$response['id'];
            $card->card_id =$response['card']['id'];
            // $card->customer_id =$response['card']['customer'];
            $card->last_four = $response['card']['last_four'];
            $card->first_six = $response['card']['first_six'];
            $card->exp_month = $response['card']['exp_month'];
            $card->exp_year = $response['card']['exp_year'];
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
