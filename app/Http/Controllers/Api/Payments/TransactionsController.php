<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TapService;
use App\Models\Payments\Transaction;
use App\Models\System\System;
use App\Services\PaymentService;
use App\Models\Payments\Card;

class TransactionsController extends Controller
{
    protected $paymentService;
    protected $tapService;

    public function __construct(PaymentService $paymentService , TapService $tapService)
    {
        $this->paymentService = $paymentService;
        $this->tapService = $tapService;
    }

    public function createTransaction(Request $request)
    {
        // return $request->all();
        sleep(2);
        $transaction =  $this->paymentService->createTransaction($request->payment_id , $request->provider_id);
        return success($transaction , System::HTTP_OK);
        return route('buy',[$transaction->id , 'tok_pvEh5523187TMt02hM9G31']);
    }

    public function buy($transaction , $card)
    {
        // return $card;
        // return $this->tapService->createTokenFromCard($card);
        $token = null;
        $AllowCard = false;
        $card = Card::find($card);
        // return $card;
        // return $card;
        if($card->token){
            $token = $card->token;
        }else{
            $token = $this->tapService->createTokenFromCard($card->id);
            $AllowCard = true;
            if($token){
                $card->token = $token;
                $card->save();
            }
        }
        // return $token;        
        if($card->token){
            $transaction = Transaction::find($transaction);
            
            if($transaction){
                // return $token;
                return $this->tapService->createCharge($transaction , $token ,  $AllowCard ? $card : null);
            }
        }
        
    }
}
