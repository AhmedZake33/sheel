<?php

namespace App\Services;

use App\Models\Payments\Payment;
use App\Models\Payments\Transaction;
use App\Models\System\System;
use App\models\Request;
use Auth;

class PaymentService extends Base {

    public static function buy($transaction , $token)
    {
        $payment = $transaction->payment;
        // return $payment;
        $request = Request::where('payment_id',$payment->id)->first();
        // return $request;
        $user = $request->user;
        $client = new \GuzzleHttp\Client();
        // $data = json_encode(array('amount' =>9, 'currency' => 'KWD', "threeDSecure"=>true , "customer_initiated"=>true,"description"=>"Test Description", "customer"=>["first_name"=>"test","middle_name"=>"test","last_name"=>"test","email"=>'$user->email',"phone"=>["country_code"=>"965","number"=>"51234567"]] , "source"=>["id"=>"tok_gJNf43231744NEuc2Af9J160"],"post"=>["url"=>"http://your_website.com/post_url"],"redirect"=>["url"=>"http://your_website.com/redirect_url"] ));
        $data = json_encode(array('amount' => $payment->amount , 'currency' => 'AED', "threeDSecure"=>true , "customer_initiated"=>true,"description"=>"Test Description", "customer"=>["first_name"=>$user->name,"middle_name"=>$user->name,"last_name"=>$user->name,"email"=>$user->email,"phone"=>["country_code"=>"965","number"=>"51234567"]] , "source"=>["id"=>$token],"post"=>["url"=>"http://your_website.com/post_url"],"redirect"=>["url"=>"http://127.0.0.1:8000/callback/$transaction->id"] ));
        $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
            'body' => $data,
            // 'body' => '{"amount":1,"currency":"KWD","customer_initiated":true,"threeDSecure":true,"save_card":true,"description":"Test Description","metadata":{"udf1":"Metadata 1"},"reference":{"transaction":"txn_01","order":"ord_01"},"receipt":{"email":true,"sms":true},"customer":{"first_name":"test","middle_name":"test","last_name":"test","email":"test@test.com","phone":{"country_code":965,"number":51234567}},"source":{"id":"tok_SsKT52231141sYtx217T9M588"},"post":{"url":"https://webhook.site/a19b3c5e-e867-49ed-bb46-addcdb8c1487"},"redirect":{"url":"http://127.0.0.1:8000/callback/$transaction->id"}}',

            'headers' => [
              'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
              'accept' => 'application/json',
              'content-type' => 'application/json',
            ],
          ]);
        $result = $response->getBody(); 
        $data =  json_decode($result, true);
        $transaction->data = $result;
        if($data['status'] == 'CAPTURED' && $data['amount'] == $transaction->amount){
            $transaction->status = 1;
            $transaction->paid = $data['amount'];
            if($transaction->paid >= $transaction->amount){
                $payment->status = 1;
                $payment->save();
            }
            return success([],200,'success');            
        }
        $transaction->save();
        // return response()->json($result);

        echo  $result;
        
        return $data['redirect']? $data['redirect']['url']:null;
        // return redirect()->to($data['transaction']['url']);   
    } 


    public function createToken($request)
    {
        $data = json_encode(['card' => ['number' => $request->card_number , 'exp_month' => $request->exp_month , 'exp_year' => $request->exp_year , 'cvc' => $request->cvc , 'name' => $request->name  ]]);
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
            // "body" => $data , 
            'body' => '{"card":{"number":5123450000000008,"exp_month":1,"exp_year":2039,"cvc":100,"name":"test user","address":{"country":"Kuwait","line1":"Salmiya, 21","city":"Kuwait city","street":"Salim","avenue":"Gulf"}},"client_ip":"192.168.1.20"}',
            'headers' => [
                'Authorization' => 'Bearer sk_test_9KRJPwZOzVhpcuomeA1y7L5d',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);
        $result =   $response->getBody();
        $data =  json_decode($result, true);
        return $data;
        return gettype($data);


        // $client = new \GuzzleHttp\Client();
        // $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
        //     'body' => '{"card":{"address_city":"Some city","address_country":"Some country","address_line1":"First line","address_line2":"Second line","address_state":"Royal State","address_zip":"007","crypted_data":"Z6cYic0X0I71h9ABUxreudcF76iz5uthier1Ec4YPVv0WsId+F1DbeU0llRgrnXTlGtzzrmWiP+I8owc+Zq0GiYbFYs4se0zYcfLEqDrXgdGiQ+X0v8rwObhD/4ef+OARLrH/rfka0mVxtzTMGzxRivGlLQy27qyj0KtS+/ShY4TQ930iGVOzFOn5VdL8w1H/b6+9bgKtMlD8tGqy624Q2uz8pVHyGvmpuEa0yPoJEjYGC/9qUI6/KQXDw9EWw4ZbhwXNBKNFTUJjvvVcrMVpvktS3/T0PYFCRIpKXvY7wEXewrqG9/nDochyzjJtWPTz/eQ+bl8D26HXqgmb8gjoQ=="}}',
        //     'headers' => [
        //         'Authorization' => 'Bearer sk_test_9KRJPwZOzVhpcuomeA1y7L5d',
        //         'accept' => 'application/json',
        //         'content-type' => 'application/json',
        //     ],
        // ]);

        // $result =  $response->getBody();
        // $data =  json_decode($result, true);
        // return $data;
    }

    public function createTransaction($payment , $provider_id)
    {
        // get payment
        $payment = Payment::find($payment);
        if($payment){
            $transaction = new Transaction();
            $transaction->payment_id = $payment->id;
            $transaction->provider_id = $provider_id;
            $transaction->amount = $payment->amount;
            $transaction->save();
            return $transaction;
        }else{
            $message  = ($this->lang == 'ar')?  'حدث خطأ' : 'something went wrong';
            return success([] , System::HHTP_Unprocessable_Content ,$message);
        }
    }

    public static function retriveToken($card)
    {
        return $card->id;
    } 

    public static function createCustomer($data)
    {

    }
}