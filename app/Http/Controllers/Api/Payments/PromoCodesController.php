<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment\PromoCode;
use App\Models\System\System;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PromoCodesController extends Controller
{
    public function check(Request $request)
    {
        $promocode = PromoCode::where('code',$request->code)->first(); 
        if(!$promocode){
            $message = $this->lang == 'en' ? 'Promo Code Valid':'رمز ترويجي صحيح';
            return success($promocode->data(System::DATA_BRIEF) , System::HTTP_OK , $message );
        }
        $counts =  $promocode->payments->count();
        $now = Carbon::now()->toDateString();
        if($promocode->counts > $counts && $now < $promocode->expiration_date){
            
        }
        $message = $this->lang == 'en' ? 'Promo Code Not Valid or Expired':' رمز ترويجي غير صحيح او منتهي ';
        return success([],System::HHTP_Unprocessable_Content ,$message);
        
    }
}
