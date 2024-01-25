<?php 


namespace App\Services;

use App\models\Request;
use App\Models\Payments\Card;
use App\Services\Base;

class TapService extends base {

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
    // create saved card 
    public static function createTokenFromCard($card_id)
    {
        $card = Card::find($card_id);
        $data = json_encode(['saved_card' => ["card_id" => $card->card_id ,"customer_id"=> $card->customer_id] , "client_id" => "127.0.0.1"]);
        $secret = env('TAP_SECRET_KEY');
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
        return $data['id'];
    }   
    // create token from saved card


    // create charge
    public static function createCharge($transaction , $token , $card = null)
    {
        $payment = $transaction->payment;
        $request = Request::where('payment_id',$payment->id)->first();
        $user = $request->user;
        $client = new \GuzzleHttp\Client();
        if($card){
            $data = json_encode(
                array(
                    "amount" => $payment->amount , 
                    "currency" => "AED",
                    "threeDSecure"=>true ,
                    "save_card"=>true,
                    "customer_initiated"=>false,
                    "payment_agreement"=> [
                        "id"=> "$card->payment_agreement_id",
                        "contract"=>[
                        "id"=> "$card->card_id"
                    ]],
                    "description"=>"Test Description", 
                    "customer"=>
                        ["first_name"=>$user->name,
                        "middle_name"=>$user->name,
                        "last_name"=>$user->name,
                        "email"=>$user->email,
                        "phone"=>["country_code"=>"965","number"=>"51234567"]
                        ] ,
                    "source"=>["id"=>"$token"],
                    "post"=>["url"=>"https://fd50-197-165-146-253.ngrok-free.app/callback/{$transaction->id}"],
                    "redirect" => ["url" =>  route('callback',[$transaction->id])]
                )
            );
        }else{
            $data = json_encode(
                array(
                    "amount" => $payment->amount , 
                    "currency" => "AED",
                    "threeDSecure"=>true ,
                    "save_card"=>true,
                    "customer_initiated"=>true,
                    "description"=>"Test Description", 
                    "customer"=>
                        ["first_name"=>$user->name,
                        "middle_name"=>$user->name,
                        "last_name"=>$user->name,
                        "email"=>$user->email,
                        "phone"=>["country_code"=>"965","number"=>"51234567"]
                        ] ,
                    "source"=>["id"=>"$token"],
                    "post"=>["url"=>"https://fd50-197-165-146-253.ngrok-free.app/callback/{$transaction->id}"],
                    "redirect" => ["url" =>  route('callback',[$transaction->id])]
                )
            );
        }
        
        $secret = env('TAP_SECRET_KEY');
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
        return  $result;
        
        return $data['redirect']? $data['redirect']['url']:null;
        // return redirect()->to($data['transaction']['url']);
        
    } 

    public function getCharge($charge_id)
    {
        $client = new \GuzzleHttp\Client();
        $secret = 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ';
        // $secret = env('TAP_SECRET_KEY');
        $response = $client->request('GET', "https://api.tap.company/v2/charges/$charge_id", [
            'headers' => [
                'Authorization' => "Bearer $secret",
                'accept' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }


}