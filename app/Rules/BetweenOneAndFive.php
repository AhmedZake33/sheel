<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BetweenOneAndFive implements Rule
{

    protected $lang = 'en';

    public function __construct()
    {
        $this->lang = request()->header('lang')?request()->header('lang') : 'en';
    }

    public function passes($attribute, $value)
    {
        return $value >= 1 && $value <= 5;
    }

    public function message()
    {
        return ['ar' => 'التقيم لابد ان يكون بين رقمين 1 و 5' , 'en' => 'Rate Must be Between in 1 and 5'][$this->lang];
    }
}
