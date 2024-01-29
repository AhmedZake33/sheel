<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\System\System;

class registerRequest extends FormRequest
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
            'name' => 'required|regex:/^[a-zA-Z]+$/',
            'email' => [
                'required'
                ,'email'
                ,'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',
                Rule::unique('users')->where(function($q){
                    return $q->where('status',0);
                })
            ],
            'mobile' => ['required','regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/' , Rule::unique('users')->where(function($q){
                return $q->where('status' , 0);
            })],
            'mobile_code' => 'required|regex:/^\+\d{1,4}$/'
        ];
    }

    public function messages()
    {
        return [
            'name.required' =>  ['ar' => 'الاسم مطلوب للتسجيل' , 'en' => 'Name is required For Register'][$this->lang],
            'name.regex' =>    ['ar' => 'الاسم لابد ان يحتوي علي حروف' , 'en' => 'The name must contain letters'][$this->lang],
            'email.required' =>  ['ar' => 'البريد الالكتروني مطلوب للتسجيل' , 'en' => 'ُE-mail is required For Register'][$this->lang],
            'email.unique' =>  ['ar' => 'البريد الالكتروني مطلوب مستخدم من قبل' , 'en' => 'ُE-mail is used before'][$this->lang],
            'email.email' =>  ['ar' => 'البريد الالكتروني خاطئ' , 'en' => 'ُE-mail Format is incorrect'][$this->lang],
            'mobile_code.required' =>  ['ar' => 'كود الموبايل مطلوب' , 'en' => 'Mobile Code is required'][$this->lang],
            'mobile_code.regex' => ['ar' => 'الكود الموبايل خاطئ' , 'en' => 'Mobile Code Format is Wrong'][$this->lang]
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $message = ($this->lang == 'en')? 'The data entered is incorrect' : 'البيانات المدخلة غير صحيحة' ;
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success(null , System::HHTP_Unprocessable_Content , $message );

        throw new HttpResponseException($response);
    }
}
