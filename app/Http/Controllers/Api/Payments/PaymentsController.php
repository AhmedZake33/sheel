<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CardRequest;
use App\Models\Payments\Card;
use App\Models\Payments\Transaction;
use App\Models\System\System;
use App\Models\Request as RequestModel;
use App\Services\PaymentService;
use App\Services\TapService;
use DB;
use App\Models\Notification;
use Illuminate\Support\Facades\Redirect;
class PaymentsController extends Controller
{

    protected  $paymentService = null;
    protected  $tapService = null;

    public function __construct(PaymentService $paymentService , TapService $tapService)
    {
        $this->paymentService = $paymentService;
        $this->tapService = $tapService;
    }

    public function buy($id)
    {
        $request = RequestModel::find($id);
        // $data = [];
      // $data['amount'] = 1;
        // $data['currency'] = 'USD';
        // $data['customer']['first_name'] = 'zaki';
        // $data['customer']['phone']['contry_code'] = '+20';
        // $data['customer']['phone']['number'] = '1229263929';
        // $data['customer']['address'] = 'eegypt';
        // $data['customer']['email'] = 'ahmed.zake333@gmail.com';
        // $data['source']['id'] = 'src_card';
        // $data['redirect']['url'] = 'http://127.0.0.1:8000/callback';

        // $headers = [
        //     'Authorization' => 'Bearer sk_test_9KRJPwZOzVhpcuomeA1y7L5d',
        //     'content-type' => 'application/json',
        // ]; 
        
        
        // $ch = curl_init();
        // $url = "https://api.tap.company/v2/charges";
        // curl_setopt($ch,CURLOPT_URL , $url);
        // curl_setopt($ch,CURLOPT_POST , true);
        // curl_setopt($ch,CURLOPT_POSTFIELDS , json_encode($data));
        // curl_setopt($ch,CURLOPT_HTTPHEADER , $headers);
        // curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
        // $output = curl_exec($ch);
        // curl_close($ch);
        // dd(json_decode($output));

        // $payment = $request->payment;
        // $user = $request->user;

          
        // guzzle 
        // $client = new \GuzzleHttp\Client();
        // $data = json_encode(array('amount' => 155 , 'currency' => 'AED', "threeDSecure"=>true , "customer_initiated"=>true,"description"=>"Test Description", "customer"=>["first_name"=>"test","middle_name"=>"test","last_name"=>"test","email"=>'$user->email',"phone"=>["country_code"=>"965","number"=>"51234567"]] , "source"=>["id"=>"src_all"],"post"=>["url"=>"http://your_website.com/post_url"],"redirect"=>["url"=>"http://your_website.com/redirect_url"] ));
        // // dd($data);
        // $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
        //     // 'body' => '{"amount":1,"currency":"KWD","customer_initiated":true,"threeDSecure":true,"save_card":false,"description":"Test Description","metadata":{"udf1":"Metadata 1"},"reference":{"transaction":"txn_01","order":"ord_01"},"receipt":{"email":true,"sms":true},"customer":{"first_name":"test","middle_name":"test","last_name":"test","email":"test@test.com","phone":{"country_code":965,"number":51234567}},"source":{"id":"src_all"},"post":{"url":"http://your_website.com/post_url"},"redirect":{"url":"http://your_website.com/redirect_url"},"method":"POST","path":"/","client_ip":"168.187.154.71","url":"https://1f3b186efe31e8696c144578816c5443.m.pipedream.net/","headers":{"host":"1f3b186efe31e8696c144578816c5443.m.pipedream.net","content-length":"2915","content-type":"application/json","hashstring":"16250de7d6c99b7cbb9866f91c348791a5a1dca3649d6ce64f0699c97da49a90","hash":"67f62adfb3153f470f90738c4546d10795cbfc070e7f929113fbd19488535e71"},"bodyRaw":"{\\r\\n  \\"id\\": \\"chg_TS032420221429Km940109459\\",\\r\\n  \\"object\\": \\"charge\\",\\r\\n  \\"live_mode\\": false,\\r\\n  \\"api_version\\": \\"V2\\",\\r\\n  \\"method\\": \\"POST\\",\\r\\n  \\"status\\": \\"CAPTURED\\",\\r\\n  \\"amount\\": 1.00,\\r\\n  \\"currency\\": \\"USD\\",\\r\\n  \\"threeDSecure\\": true,\\r\\n  \\"card_threeDSecure\\": false,\\r\\n  \\"save_card\\": false,\\r\\n  \\"merchant_id\\": \\"\\",\\r\\n  \\"product\\": \\"\\",\\r\\n  \\"statement_descriptor\\": \\"Sample\\",\\r\\n  \\"description\\": \\"Test Description\\",\\r\\n  \\"metadata\\": {\\r\\n    \\"udf1\\": \\"test 1\\",\\r\\n    \\"udf2\\": \\"test 2\\"\\r\\n  },\\r\\n  \\"transaction\\": {\\r\\n    \\"authorization_id\\": \\"294986\\",\\r\\n    \\"timezone\\": \\"UTC+03:00\\",\\r\\n    \\"created\\": \\"1662042581741\\",\\r\\n    \\"expiry\\": {\\r\\n      \\"period\\": 30,\\r\\n      \\"type\\": \\"MINUTE\\"\\r\\n    },\\r\\n    \\"asynchronous\\": false,\\r\\n    \\"amount\\": 0.318,\\r\\n    \\"currency\\": \\"KWD\\"\\r\\n  },\\r\\n  \\"reference\\": {\\r\\n    \\"track\\": \\"tck_TS032420221429Dj490109459\\",\\r\\n    \\"payment\\": \\"2401221429094596907\\",\\r\\n    \\"gateway\\": \\"123456789\\",\\r\\n    \\"acquirer\\": \\"224411294986\\",\\r\\n    \\"transaction\\": \\"txn_0001\\",\\r\\n    \\"order\\": \\"ord_0001\\"\\r\\n  },\\r\\n  \\"response\\": {\\r\\n    \\"code\\": \\"000\\",\\r\\n    \\"message\\": \\"Captured\\"\\r\\n  },\\r\\n  \\"security\\": {\\r\\n    \\"threeDSecure\\": {\\r\\n      \\"id\\": \\"3ds_TS034120221429o2Q50109741\\",\\r\\n      \\"status\\": \\"Y\\"\\r\\n    }\\r\\n  },\\r\\n  \\"acquirer\\": {\\r\\n    \\"response\\": {\\r\\n      \\"code\\": \\"00\\",\\r\\n      \\"message\\": \\"Approved\\"\\r\\n    }\\r\\n  },\\r\\n  \\"gateway\\": {\\r\\n    \\"response\\": {\\r\\n      \\"code\\": \\"0\\",\\r\\n      \\"message\\": \\"Transaction Approved\\"\\r\\n    }\\r\\n  },\\r\\n  \\"card\\": {\\r\\n    \\"object\\": \\"card\\",\\r\\n    \\"first_six\\": \\"512345\\",\\r\\n    \\"scheme\\": \\"MASTERCARD\\",\\r\\n    \\"brand\\": \\"MASTERCARD\\",\\r\\n    \\"last_four\\": \\"0008\\"\\r\\n  },\\r\\n  \\"receipt\\": {\\r\\n    \\"id\\": \\"202501221429096826\\",\\r\\n    \\"email\\": false,\\r\\n    \\"sms\\": true\\r\\n  },\\r\\n  \\"customer\\": {\\r\\n    \\"id\\": \\"cus_TS064220221429Zl940109178\\",\\r\\n    \\"first_name\\": \\"test\\",\\r\\n    \\"last_name\\": \\"test\\",\\r\\n    \\"email\\": \\"test@test.com\\",\\r\\n    \\"phone\\": {\\r\\n      \\"country_code\\": \\"965\\",\\r\\n      \\"number\\": \\"50000000\\"\\r\\n    }\\r\\n  },\\r\\n  \\"merchant\\": {\\r\\n    \\"country\\": \\"KW\\",\\r\\n    \\"currency\\": \\"KWD\\",\\r\\n    \\"id\\": \\"599424\\"\\r\\n  },\\r\\n  \\"source\\": {\\r\\n    \\"object\\": \\"token\\",\\r\\n    \\"type\\": \\"CARD_NOT_PRESENT\\",\\r\\n    \\"payment_type\\": \\"CREDIT\\",\\r\\n    \\"payment_method\\": \\"MASTERCARD\\",\\r\\n    \\"channel\\": \\"INTERNET\\",\\r\\n    \\"id\\": \\"tok_ZqrX39221129u4bb1fA8U654\\"\\r\\n  },\\r\\n  \\"redirect\\": {\\r\\n    \\"status\\": \\"PENDING\\",\\r\\n    \\"url\\": \\"http://your_website.com/redirect_url\\"\\r\\n  },\\r\\n  \\"post\\": {\\r\\n    \\"attempt\\": 1,\\r\\n    \\"status\\": \\"PENDING\\",\\r\\n    \\"url\\": \\"https://1f3b186efe31e8696c144578816c5443.m.pipedream.net\\"\\r\\n  },\\r\\n  \\"activities\\": [\\r\\n    {\\r\\n      \\"id\\": \\"activity_TS034220221429l2N50109209\\",\\r\\n      \\"object\\": \\"activity\\",\\r\\n      \\"created\\": 1662042581741,\\r\\n      \\"status\\": \\"INITIATED\\",\\r\\n      \\"currency\\": \\"USD\\",\\r\\n      \\"amount\\": 1.00,\\r\\n      \\"remarks\\": \\"charge - created\\"\\r\\n    },\\r\\n    {\\r\\n      \\"id\\": \\"activity_TS040620221430Re2o0109626\\",\\r\\n      \\"object\\": \\"activity\\",\\r\\n      \\"created\\": 1662042606626,\\r\\n      \\"status\\": \\"CAPTURED\\",\\r\\n      \\"currency\\": \\"KWD\\",\\r\\n      \\"amount\\": 0.318,\\r\\n      \\"remarks\\": \\"charge - captured\\"\\r\\n    }\\r\\n  ],\\r\\n  \\"auto_reversed\\": false\\r\\n}","body":{"id":"chg_TS032420221429Km940109459","object":"charge","live_mode":false,"api_version":"V2","method":"POST","status":"CAPTURED","amount":1,"currency":"USD","threeDSecure":true,"card_threeDSecure":false,"save_card":false,"merchant_id":"","product":"","statement_descriptor":"Sample","description":"Test Description","metadata":{"udf1":"test 1","udf2":"test 2"},"transaction":{"authorization_id":"294986","timezone":"UTC+03:00","created":"1662042581741","expiry":{"period":30,"type":"MINUTE"},"asynchronous":false,"amount":0.318,"currency":"KWD"},"reference":{"track":"tck_TS032420221429Dj490109459","payment":"2401221429094596907","gateway":"123456789","acquirer":"224411294986","transaction":"txn_0001","order":"ord_0001"},"response":{"code":"000","message":"Captured"},"security":{"threeDSecure":{"id":"3ds_TS034120221429o2Q50109741","status":"Y"}},"acquirer":{"response":{"code":"00","message":"Approved"}},"gateway":{"response":{"code":"0","message":"Transaction Approved"}},"card":{"object":"card","first_six":"512345","scheme":"MASTERCARD","brand":"MASTERCARD","last_four":"0008"},"receipt":{"id":"202501221429096826","email":false,"sms":true},"customer":{"id":"cus_TS064220221429Zl940109178","first_name":"test","last_name":"test","email":"test@test.com","phone":{"country_code":"965","number":"50000000"}},"merchant":{"country":"KW","currency":"KWD","id":"599424"},"source":{"object":"token","type":"CARD_NOT_PRESENT","payment_type":"CREDIT","payment_method":"MASTERCARD","channel":"INTERNET","id":"tok_ZqrX39221129u4bb1fA8U654"},"redirect":{"status":"PENDING","url":"http://your_website.com/redirect_url"},"post":{"attempt":1,"status":"PENDING","url":"https://1f3b186efe31e8696c144578816c5443.m.pipedream.net"},"activities":[{"id":"activity_TS034220221429l2N50109209","object":"activity","created":1662042581741,"status":"INITIATED","currency":"USD","amount":1,"remarks":"charge - created"},{"id":"activity_TS040620221430Re2o0109626","object":"activity","created":1662042606626,"status":"CAPTURED","currency":"KWD","amount":0.318,"remarks":"charge - captured"}],"auto_reversed":false}}',
        //     'body' => $data,
        //     'headers' => [
        //       'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
        //       'accept' => 'application/json',
        //       'content-type' => 'application/json',
        //     ],
        //   ]);
          
        // $result = $response->getBody();
        // $data =  json_decode($result, true);
        // return $data['transaction'];
        // return redirect()->to($data['transaction']['url']);


        return $this->paymentService->buy($request);
        

    }

    public function callBack($transaction)
    {
        // dd(request()->all());
        $charge_id =  $_GET['tap_id'];

        // DB::table('testcallback')->insert(['data' => 'test data']);
        $response = $this->tapService->getCharge($charge_id);
        $result = json_decode($response);
        // return $result;
        if($result->status == "CAPTURED"){
           // update transaction and payment and request
            $transaction = Transaction::find($transaction);
            $transaction->updateStatus($result);
            $payment = $transaction->payment;
            $requestModel = RequestModel::where('payment_id',$payment->id)->first();
            $requestModel->startFindProvider();
            // $provider = ($requestModel->CurrentProvider && $requestModel->CurrentProvider->provider->user) ? $requestModel->CurrentProvider->provider->user : null;
            // if($provider){
            //     $title = ['ar' => 'User was Paid the amount' , 'en' => 'User was Paid the amount'];
            //     Notification::createNotification($provider->id , $requestModel->id , $title);
            // }
            return Redirect::to(domain() .  '/success');
        }else{
            return Redirect::to(domain() .  '/fail');
        }
    }

    public function callbackSavedCard($userId)
    {
        $charge_id =  $_GET['tap_id'];
        $response = $this->paymentService->getCharge($charge_id);
        $result = json_decode($response , true);
        if($result['status'] == "CAPTURED"){
            // save card
            $card = new Card();
            $card->user_id = $userId;
            $card->card_id =$result['card']['id'];
            $card->customer_id = $result['customer']['id'];
            $card->last_four = $result['card']['last_four'];
            $card->first_six = $result['card']['first_six'];
            $card->payment_agreement_id = $result['payment_agreement']['id'];
            $card->save();
            // return $result;

            // refund 
            $this->paymentService->refund($result);
            return Redirect::to(domain() .  '/success');            
           
        }else{
            return Redirect::to(domain() .  '/fail');
        }
    }
}
