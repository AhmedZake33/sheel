<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportRequest;
use App\Mail\ExampleMail;
use App\Mail\SupportMail;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Mail;

class SupportsController extends Controller
{
    public function create(SupportRequest $request)
    {
        $user = Auth::user();
        $support = $user->supports()->create($request->all());
        // send support by email
        // return get_class($support);
    
        try{
            Mail::to('ahmed.zaki@elnadycompany.com')->send(new SupportMail($support));
        }catch(\Exception $ex){
            return $ex->getMessage();
        }

        return $support;
    }
}
