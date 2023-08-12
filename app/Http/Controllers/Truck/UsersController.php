<?php

namespace App\Http\Controllers\Truck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class UsersController extends Controller 
{
    public function regsiterTruck(Request $request)
    {
        return response()->json($request->all());
    }
}