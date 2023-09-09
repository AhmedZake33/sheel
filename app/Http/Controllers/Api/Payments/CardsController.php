<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Models\Payment\Card;
use Illuminate\Http\Request;
use App\Models\System\System;
class CardsController extends Controller
{
    public function addCard(CardRequest $request)
    {
        $user = auth()->user();
        // if($user->card){
        //     $message  = ($this->lang == 'ar')?  'أنت بالفعل لديك بطاقة' : 'You already added Card';
        //     return success([] , System::HHTP_Unprocessable_Content ,$message);
        // }
        $validated = $request->validated();
        $user = auth()->user();
        $user->cards()->save( new Card($validated));
        $message  = ($this->lang == 'ar')?  'تم إضافة البطاقة بنجاح' : 'Card added Successfully';
        return success([] , System::HHTP_Unprocessable_Content ,$message);
        
    }

    public function editCard(CardRequest $request , Card $card)
    {
        return $card;
    }
}
