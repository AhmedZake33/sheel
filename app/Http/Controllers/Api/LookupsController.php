<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\System;
use App\Models\Service;

class LookupsController extends Controller
{
    public function get()
    {   
        $lookups =  [];
        $services = Service::select('id','name','name_local')->get();
        $lookups['services'] = $services;
        return success($lookups , System::HTTP_OK,'success');
    }
}
