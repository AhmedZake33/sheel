<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\System\System;

class ResendCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    
    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'mobile' => [
                'required','regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/',
            ]
            
        ];
    }

    public function messages()
    {
        return [
            'mobile.required' =>  ['ar' => 'رقم الموبايل مطلوب للتسجيل' , 'en' => 'Phone Number is required For Register'][$this->lang],
            'mobile.regex' =>    ['ar' => 'رقم الموبايل غير صحيح' , 'en' => 'Phone number is incorrect'][$this->lang],           
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $message = ($this->lang == 'en')? 'The data entered is incorrect' : 'البيانات المدخلة غير صحيحة' ;
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success([] , System::HHTP_Unprocessable_Content , $message );

        throw new HttpResponseException($response);
    }
}
