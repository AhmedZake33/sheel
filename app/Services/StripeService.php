<?php

namespace App\Services;

class StripeService extends Base {

    public function createCharge()
    {
        $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        return $stripe->charges->create([
            'amount' => 2000,
            'currency' => 'usd',
            'source' => 'tok_1PKo062eZvKYlo2CTHTJj4Sp',
            
            'description' => 'My First Test Charge (created for API docs at https://www.stripe.com/docs/api)',
        ]);
    }

    public function createCard()
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
}