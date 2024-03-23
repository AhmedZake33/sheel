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
            'name' => 'required|regex:/^[a-zA-Z ]+$/',
            'email' => [
                'required'
                ,'email'
                ,'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',
                Rule::unique('users')->ignore(auth()->id())->where(function($q){
                    return $q->where('status',0);
                })
            ],
            // 'mobile' => ['required','regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/' , Rule::unique('users')->ignore(auth()->id())->where(function($q){
            //     return $q->where('status' , 0);
            // })],
            'profile_photo' => 'nullable|image',
            // 'mobile_code' => 'required|regex:/^\+\d{1,4}$/'
        ];
    }

    public function messages()
    {
        // message is here
        return [
            'name.regex' =>    ['ar' => 'الاسم لابد ان يحتوي علي حروف' , 'en' => 'The name must contain letters'][app()->getLocale()],
            'email.unique' =>  ['ar' => 'البريد الالكتروني مطلوب مستخدم من قبل' , 'en' => 'ُE-mail is used before'][app()->getLocale()],
            'email.email' =>  ['ar' => 'البريد الالكتروني خاطئ' , 'en' => 'ُE-mail Format is incorrect'][app()->getLocale()],
            // 'mobile_code.required' =>  ['ar' => 'كود الموبايل مطلوب' , 'en' => 'mobile Code is required'][app()->getLocale()],
            // 'mobile_code.regex' => ['ar' => 'الكود الموبايل خاطئ' , 'en' => 'mobile Code Format is Wrong'][app()->getLocale()]
            
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $message = (app()->getLocale() == 'en')? 'The data entered is incorrect' : 'البيانات المدخلة غير صحيحة' ;
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success(null , System::HHTP_Unprocessable_Content , $message );

        throw new HttpResponseException($response);
    }
}
