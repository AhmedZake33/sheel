<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequest;
use Illuminate\Http\Request;

class SupportsController extends Controller
{
    public function create(SupportRequest $request)
    {
        return $request->all();
    }
}
