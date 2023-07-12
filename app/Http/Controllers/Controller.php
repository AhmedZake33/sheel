<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $lang = 'en';

    public function __construct()
    {
        $this->lang = request()->header('lang')?request()->header('lang') : 'en';
    }
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
