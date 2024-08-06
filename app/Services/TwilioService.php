<?php
namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN')
        );
    }

    public function sendSms($to, $message)
    {
        $this->twilio->messages->create($to, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => $message
        ]);
    }

    public function send()
    {
        $sid    = "AC468dc32d37f3ca61d3da02ff7fb9cbcf";
        $token  = "3297f0f8906aed9736c82ef802bb1598";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
        ->create("+201229263919", // to
            array(
            "from" => "+19388882772",
            "body" => "Your Message"
            )
        );

        return ($message->sid);
    }

    public function fetch()
    {

        $phoneNumber = '+201229263919'; // Replace with the phone number you want to check
        $country = 'EG'; // ISO-3166-1 alpha-2 country code

        $response = $this->twilio->lookups->v1->phoneNumbers($phoneNumber)->fetch(['countryCode' => $country]);
        // return $response;
        if ($response->sms && $response->sms->international) {
            echo "This number can send international SMS messages to $country.";
        } else {
            echo "This number cannot send international SMS messages to $country.";
        }
    }
}
