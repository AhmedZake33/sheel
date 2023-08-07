<?php

namespace App\Services;

class Base 
{
    protected $lang = 'en';

    public function __construct()
    {
        $this->lang = request()->header('lang')?request()->header('lang') : 'en';
    }
}

?>