<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Models\Payments\Card;
use Illuminate\Http\Request;
use App\Models\System\System;
use App\Services\TapService;
use App\Services\PaymentService;
use Auth;
class CardsController extends Controller
{
    protected  $paymentService = null;

    public function __construct(PaymentService $paymentService)
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
        if($response){
            return $this->paymentService->saveCard($user , $response);
        }       
    }

    public function editCard(CardRequest $request , Card $card)
    {
        return $card;
    }
}
