<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\System\System;


class verifyRequest extends FormRequest
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
            'otp_code' => 'required|regex:/^[0-9]{5}$/',
            'secret' => 'required'
        ];
    }


    public function messages()
    {
        return [
            'otp_code.required' => ['ar' => 'الكود مطلوب' , 'en' => 'Code Is Required'][$this->lang],
            'otp_code.regex' => ['ar' => 'الكود لابد ان يكون ارقام مكون من خمس ارقام' , 'en' => 'Code Must Be Numbers only and Consist Of 5 numbers'][$this->lang],
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
 