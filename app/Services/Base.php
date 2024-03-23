<?php

namespace App\Services;

class Base 
{
    protected $lang = 'en';

    public function __construct()
    {
        app()->getLocale() = request()->header('lang')?request()->header('lang') : 'en';
    }
}

?>