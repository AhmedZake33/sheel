<?php

namespace App\Services;

class StripeService extends Base {

    public static function createCharge()
    {
        $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        return $stripe->charges->create([
            'amount' => 2000,
            'currency' => 'usd',
            'source' => 'tok_visa',
            'description' => 'My First Test Charge (created for API docs at https://www.stripe.com/docs/api)',
        ]);
    }

    public static function createCard($request)
    {
        $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        $stripe->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 10,
                'exp_year' => 2024,
                'cvc' => '314',
            ],
        ]);
    } 
}