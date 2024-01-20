<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\System\System;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
     // emairate_id_front
    // emairate_id_back
    // drive_photo 
    // RTA_card_front
    // RTA_card_back
    // vehicle_registration_form 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'regex:/^[a-zA-Z]+$/',
            'email' => [
                'nullable',
                Rule::unique('users')->where(function($q){
                    return $q->where('status',0);
                })
            ],
            'mobile' => ['regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/' , Rule::unique('users')->where(function($q){
                return $q->where('status' , 0);
            })],
            'profile_photo' => 'max:2000|mimes:jpeg,png,doc,docs,pdf',
        ];
    }

    public function messages()
    {
        // message is here
        return [
            'name.regex' =>    ['ar' => 'الاسم لابد ان يحتوي علي حروف' , 'en' => 'The name must contain letters'][$this->lang],
            'email.unique' =>  ['ar' => 'البريد الالكتروني مطلوب مستخدم من قبل' , 'en' => 'ُE-mail is used before'][$this->lang],
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
