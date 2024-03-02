<?php

namespace App\Services;

use App\Models\Payments\Payment;
use App\Models\Payments\Transaction;
use App\Models\System\System;
use App\models\Request;
use App\Models\Payments\Card;
use Auth;

class PaymentService extends Base {

    // create token and card
    public static function createToken($request)
    {
        $data = json_encode(
            ['card' => 
                ['number' => $request->card_number , 
                'exp_month' => $request->exp_month , 
                'exp_year' => $request->exp_year , 
                'cvc' => $request->cvc , 
                'name' => $request->name  
                ]
            ]
        );
        $client = new \GuzzleHttp\Client();
        $secret = env('TEST_TAP_SECRET_KEY');
        $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
            "body" => $data , 
            'headers' => [
                'Authorization' => "Bearer $secret",
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);
        $result =   $response->getBody();
        $data =  json_decode($result, true);
        return $data;
        return gettype($data);
    }

    public static function createTokenFromCard($card_id)
    {
        $card = Card::find($card_id);
        $data = json_encode(['saved_card' => ["card_id" => $card->card_id ,"customer_id"=> $card->customer_id] , "client_id" => "127.0.0.1"]);
        $secret = env('TEST_TAP_SECRET_KEY');
        $client = new \GuzzleHttp\Client();
        
        $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
        'body' => $data,
        'headers' => [
            'Authorization' => "Bearer $secret",
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        ]);

        $result =  $response->getBody();
        $data =  json_decode($result,true);
        // return $data['id'];
        if($data){
            $card->token = $data['id'];
            $card->save();
        }
    }

    public static function buy($transaction , $card , $saveCard = false)
    {
        $payment = $transaction->payment;
        // return $payment;
        $request = Request::where('payment_id',$payment->id)->first();
        // return $request;
        $user = $request->user;
        // return $user;
        $secret = env('TEST_TAP_SECRET_KEY');
        $client = new \GuzzleHttp\Client();
        $data = json_encode(array(
            'amount' => $payment->amount , 
            'currency' => 'AED', 
            "threeDSecure"=>true ,
            "save_card"=>$saveCard, 
            "customer_initiated"=>true,
            "description"=>"Test Description",
            "payment_agreement"=> [
                "id"=> "$card->payment_agreement_id",
                "contract"=>[
                "id"=> "$card->card_id"
            ]],
            "customer"=>[
                "first_name"=>$user->name,
                "middle_name"=>$user->name,
                "last_name"=>$user->name,
                "email"=>$user->email,
                "id" => $card->customer_id,
                "phone"=>[
                    "country_code"=>"965",
                    "number"=>"51234567"
                ]
            ] , 
            "source"=>[
                "id"=>$card->token
            ],
            "post"=>[
                "url"=>"http://your_website.com/post_url"
            ],
            "redirect"=>[
                "url"=> domain() .  "/callback/$transaction->id"
            ] 
        ));
        $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
            'body' => $data,
            'headers' => [
              'Authorization' => "Bearer $secret",
              'accept' => 'application/json',
              'content-type' => 'application/json',
            ],
          ]);
        $result = $response->getBody(); 
        $data =  json_decode($result, true);
        // clear token from card 
        // $card->token = null;
        // $card->save();
        return success(["url" => $data["transaction"]["url"]], System::HTTP_OK ,"success");
    }

    public static function saveCard($user ,$response)
    {
        $client = new \GuzzleHttp\Client();
        $secret = env('TEST_TAP_SECRET_KEY');
        $data = json_encode(array(
            'amount' => 1, 
            'currency' => 'AED', 
            "threeDSecure"=>true ,
            "save_card"=>true,
            "customer_initiated"=>true,
            "description"=>"Test Description",
            "customer"=>[
                "first_name"=>$user->name,
                "middle_name"=>$user->name,
                "last_name"=>$user->name,
                "email"=>$user->email,
                "phone"=>[
                    "country_code"=>"965",
                    "number"=>"51234567"
                ]
            ] , 
            "source"=>[
                "id"=>$response['id']
            ],
            "post"=>[
                "url"=>"http://your_website.com/post_url"
            ],
            "redirect"=>[
                "url"=> domain() . "/" . "callbackSavedCard/$user->id"
            ] 
        ));
        $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
            'body' => $data,
            'headers' => [
              'Authorization' => "Bearer $secret",
              'accept' => 'application/json',
              'content-type' => 'application/json',
            ],
        ]);

        $result = $response->getBody(); 
        $data =  json_decode($result, true);
        return success(["url"=>$data["transaction"]["url"]] , System::HTTP_OK ,"success");
    }


    // public function createToken($request)
    // {
    //     $data = json_encode(['card' => ['number' => $request->card_number , 'exp_month' => $request->exp_month , 'exp_year' => $request->exp_year , 'cvc' => $request->cvc , 'name' => $request->name  ]]);
    //     $client = new \GuzzleHttp\Client();

    //     $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
    //         // "body" => $data , 
    //         'body' => '{"card":{"number":5123450000000008,"exp_month":1,"exp_year":2039,"cvc":100,"name":"test user","address":{"country":"Kuwait","line1":"Salmiya, 21","city":"Kuwait city","street":"Salim","avenue":"Gulf"}},"client_ip":"192.168.1.20"}',
    //         'headers' => [
    //             'Authorization' => 'Bearer sk_test_9KRJPwZOzVhpcuomeA1y7L5d',
    //             'accept' => 'application/json',
    //             'content-type' => 'application/json',
    //         ],
    //     ]);
    //     $result =   $response->getBody();
    //     $data =  json_decode($result, true);
    //     return $data;
    //     return gettype($data);


    //     // $client = new \GuzzleHttp\Client();
    //     // $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
    //     //     'body' => '{"card":{"address_city":"Some city","address_country":"Some country","address_line1":"First line","address_line2":"Second line","address_state":"Royal State","address_zip":"007","crypted_data":"Z6cYic0X0I71h9ABUxreudcF76iz5uthier1Ec4YPVv0WsId+F1DbeU0llRgrnXTlGtzzrmWiP+I8owc+Zq0GiYbFYs4se0zYcfLEqDrXgdGiQ+X0v8rwObhD/4ef+OARLrH/rfka0mVxtzTMGzxRivGlLQy27qyj0KtS+/ShY4TQ930iGVOzFOn5VdL8w1H/b6+9bgKtMlD8tGqy624Q2uz8pVHyGvmpuEa0yPoJEjYGC/9qUI6/KQXDw9EWw4ZbhwXNBKNFTUJjvvVcrMVpvktS3/T0PYFCRIpKXvY7wEXewrqG9/nDochyzjJtWPTz/eQ+bl8D26HXqgmb8gjoQ=="}}',
    //     //     'headers' => [
    //     //         'Authorization' => 'Bearer sk_test_9KRJPwZOzVhpcuomeA1y7L5d',
    //     //         'accept' => 'application/json',
    //     //         'content-type' => 'application/json',
    //     //     ],
    //     // ]);

    //     // $result =  $response->getBody();
    //     // $data =  json_decode($result, true);
    //     // return $data;
    // }

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

    public function getCharge($charge_id)
    {
        $client = new \GuzzleHttp\Client();
        $secret = env('TEST_TAP_SECRET_KEY');
        // $secret = env('TAP_SECRET_KEY');
        $response = $client->request('GET', "https://api.tap.company/v2/charges/$charge_id", [
            'headers' => [
                'Authorization' => "Bearer $secret",
                'accept' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }

    public function refund($respnse)
    {

        $client = new \GuzzleHttp\Client();
        $secret = env('TEST_TAP_SECRET_KEY');
        $data = json_encode(array(
            "amount" => $respnse['amount'],
            "charge_id" => $respnse['id'],
            "currency" => $respnse['currency'],
            "reason" => "saved card action"
        ));
        $response = $client->request('POST', 'https://api.tap.company/v2/refunds/', [
        // 'body' => '{"charge_id":"chg_TS02A3720242314e4TQ0402473","amount":1,"currency":"AED","reason":"The product is out of stock"}',
        "body" => $data,
        'headers' => [
            'Authorization' => "Bearer $secret",
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        ]);

        // echo $response->getBody();
    }
}