<?php

namespace App\Services;

use App\Http\Controllers\Api\Payments\PaymentsController;
use App\Models\Payments\Card;
use App\Models\System\System;
use App\Models\Payments\Transaction;
use Auth;
use Illuminate\Http\Request;

class StripeService extends Base {

    // Enabling access to raw card data APIs

    public function createCharge()
    {
        $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        return $stripe->charges->create([
            'amount' => 2000,
            'currency' => 'usd',
            'source' => 'cvctok_1NkWsu2eZvKYlo2CFDm6ab7X',

            'description' => 'My First Test Charge (created for API docs at https://www.stripe.com/docs/api)',
        ]);
    }

    public function createToken($card = null)
    {
        $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        return $stripe->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 10,
                'exp_year' => 2024,
                'cvc' => '314',
            ],
        ]);
    } 

    public function createCard()
    {
        $stripe = new \Stripe\StripeClient('sk_test_26PHem9AhJZvU623DfE1x4sd');

        return $stripe->customers->createSource('cus_9s6XGDTHzA66Po', ['source' => 'tok_visa']);
    }

    public function createCustomer()
    {
        $stripe = new \Stripe\StripeClient('sk_test_26PHem9AhJZvU623DfE1x4sd');

        return $stripe->customers->create([
            "name" => "testre",
            "email" => "test@gm.cv"
        ]);
    }

    public function createSource()
    {
        try {
            $stripe = new \Stripe\StripeClient('sk_test_26PHem9AhJZvU623DfE1x4sd');
        
            $source = $stripe->customers->createSource(
                'cus_QY5PIJLkHFeTwE',
                [
                    'source' => 'tok_1PgzDm2eZvKYlo2CubleAvMk',
                ]
            );
        
            return $source;
        } catch (\Exception $e) {
            // Handle the error, e.g., log it, display a user-friendly message
            echo $e->getMessage();
        }
    }

    public function createSetupIntent()
    {
        $stripe = new \Stripe\StripeClient('sk_test_26PHem9AhJZvU623DfE1x4sd');

        return $stripe->setupIntents->create(['payment_method_types' => ['card']]);
    }


    // public function saveCard($request = null)
    // {
    //     $stripe = new \Stripe\StripeClient('sk_test_26PHem9AhJZvU623DfE1x4sd');

    //     $user = Auth::user();

    //     $customer =  $stripe->customers->create([
    //         "name" => $user->name,
    //         "email" => $user->email
    //     ]);

    //     $paymentMethod = $stripe->paymentMethods->create([
    //         'type' => 'card',
    //         'card' => [
    //             'number' => $request->card_number,
    //             'exp_month' => $request->exp_month,
    //             'exp_year' => $request->exp_year,
    //             'cvc' => $request->cvc
    //         ],
    //     ]);

    //     if($paymentMethod && $customer){
    //         $stripe->paymentMethods->attach(
    //             $paymentMethod->id,
    //             ['customer' => $customer->id]
    //         );
    //     }
       
    //     $paymentIntent = $stripe->paymentIntents->create([
    //         'amount' => 100,
    //         'currency' => 'AED',
    //         'customer' => $customer->id,
    //         'payment_method' => $paymentMethod->id,
    //         'off_session' => true,
    //         'confirm' => true,
    //     ]);

    //     if($paymentIntent->status == 'succeeded'){

    //         $card = New Card();
    //         $card->customer_id = $paymentIntent->customer;
    //         $card->last_four = $paymentMethod->card->last4;
    //         $card->exp_month = $paymentMethod->card->exp_month;
    //         $card->exp_year = $paymentMethod->card->exp_year;
    //         $card->save();
    //         $user->cards()->save($card);  
    //         return "success";       
    //     }

    // }

    public function saveCard($request = null)
    {
       
        $stripe = new \Stripe\StripeClient('sk_test_51LwUrGAaVPCFtfhoBQO5H8kJ8sGt6vrVQFFtCgnVVAKkYhjZwGhbHbiJZVUl8ovwA17zCVWHVhzFPpQpuN25ZRvt004K3tnH1x');
        $user = Auth::user();
    
        try {
            // Create a new customer
            $customer = $stripe->customers->create([
                "name" => $user->name,
                "email" => $user->email
            ]);

            // Create a new payment method
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => $request->card_number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvc
                ],
            ]);

            // Attach the payment method to the customer
            $stripe->paymentMethods->attach(
                $paymentMethod->id,
                ['customer' => $customer->id]
            );

            // Create a payment intent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => 200,
                'currency' => 'AED',
                'customer' => $customer->id,
                'payment_method' => $paymentMethod->id,
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status == 'succeeded') {
                // Save card details in your database
                $card = new Card();
                $card->customer_id = $paymentIntent->customer;
                $card->last_four = $paymentMethod->card->last4;
                $card->exp_month = $paymentMethod->card->exp_month;
                $card->exp_year = $paymentMethod->card->exp_year;
                $card->save();
                $user->cards()->save($card);

                // then refund the amount 
                $stripe->refunds->create(['payment_intent' => $paymentIntent->id]);

                return success(["addedSuccessfully" => true],System::HTTP_OK , "success add card");
            } else {
                return "Payment failed: " . $paymentIntent->status;
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Handle card errors
            return success(["addedSuccessfully"=>false],System::HHTP_Unprocessable_Content , "Card Error: " . $e->getError()->message);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Handle invalid requests
            return success(["addedSuccessfully"=>false],System::HHTP_Unprocessable_Content , "Invalid Request: " . $e->getError()->message);
        } catch (\Exception $e) {
            // Handle other errors
            return success(["addedSuccessfully"=>false],System::HHTP_Unprocessable_Content , "Error: " . $e->getMessage());
        }
    }


    public static function pay($payment = null)
    {

        $transaction = $payment->transactions()->first();

        $stripe = new \Stripe\StripeClient('sk_test_51LwUrGAaVPCFtfhoBQO5H8kJ8sGt6vrVQFFtCgnVVAKkYhjZwGhbHbiJZVUl8ovwA17zCVWHVhzFPpQpuN25ZRvt004K3tnH1x');

        // charge amount 
        if($payment->card){
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $payment->amount * 100,
                'currency' => 'AED',
                'customer' => $payment->card->customer_id,
            ]);
            $route = route('success');
            $paymentIntent = $stripe->paymentIntents->confirm(
                $paymentIntent->id,
                [
                    'payment_method' => 'pm_card_visa',
                    "return_url" => $route,
                ]
            );
           
            //  return $paymentIntent;
            if($transaction && $paymentIntent->status == "succeeded"){
               
                $data = ["transaction_id" => $transaction->id,"id" => $paymentIntent->id , "amount" => $paymentIntent->amount / 100 , "client_secret" => $paymentIntent->client_secret , "status" => $paymentIntent->status];
                $payment->card_id = null;
                $payment->save();
                self::callBack($data);
    
            }
        }
          
    }
    
    public static function callBack($data)
    {
        $transaction = $data['transaction_id'];
        $additionalData = $data;
        
        if($additionalData['status'] == "succeeded"){
           // update transaction and payment and request
            $transaction = Transaction::find($transaction);
            $transaction->updateStatus($additionalData);            
            // return Redirect::to(domain() .  '/success');
        }else{
            // return Redirect::to(domain() .  '/fail');
        }
    }
}