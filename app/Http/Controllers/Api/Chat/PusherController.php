<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\testEvent;

class PusherController extends Controller
{
    public function send()
    {
        return view('sendMessage');
        event(new testEvent('hello world' , "my-channel"));
        return "success";
    }

    public function sendMessage()
    {
        event(new testEvent('hello world' , "1"));
        return "success";
    }
}
