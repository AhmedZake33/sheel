<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\System\System;

class registerProvider extends FormRequest
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
            'name' => 'required|regex:/^[a-zA-Z]+$/',
            'email' => ['required',
                Rule::unique('users')->where(function($q){
                    return $q->where('status',0);
                })
            ],
            'mobile' => ['required','regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/' , Rule::unique('users')->where(function($q){
                return $q->where('status' , 0);
            })],
            'emairate_id_front' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'emairate_id_back' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'drive_photo' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'RTA_card_front' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'RTA_card_back' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'vehicle_registration_form' => 'required|max:2000|mimes:jpeg,png,doc,docs,pdf',
            'mobile_code' => 'required|regex:/^\+\d{1,4}$/'

        ];
    }

    public function messages()
    {
        // message is here
        return [
            'name.required' =>  ['ar' => 'الاسم مطلوب للتسجيل' , 'en' => 'Name is required For Register'][app()->getLocale()],
            'name.regex' =>    ['ar' => 'الاسم لابد ان يحتوي علي حروف' , 'en' => 'The name must contain letters'][app()->getLocale()],
            'email.required' =>  ['ar' => 'البريد الالكتروني مطلوب للتسجيل' , 'en' => 'ُE-mail is required For Register'][app()->getLocale()],
            'email.unique' =>  ['ar' => 'البريد الالكتروني مطلوب مستخدم من قبل' , 'en' => 'ُE-mail is used before'][app()->getLocale()],
            "emairate_id_front.required" => ["ar" => "صورة بطاقة الهويه الاماميه مطلوبة" , "en" => "Emirate Front ID Is required"][app()->getLocale()],
            "emairate_id_back.required" => ["ar" => "صورة بطاقة الهويه الخلفيه مطلوبة" , "en" => "Emirate Back ID Is required"][app()->getLocale()],
            "drive_photo.required" => ["ar" => "صورة رخصة القيادة  مطلوبة" , "en" => "Drive Photo Is required"][app()->getLocale()],
            "RTA_card_front.required" => ["ar" => " صورة بطاقة هيئة الطرق والمواصلات من الامام مطلوبة " , "en" => " RTA Card Front Is required"][app()->getLocale()],
            "RTA_card_back.required" => ["ar" => " صورة بطاقة هيئة الطرق والمواصلات من الامام مطلوبة "  , "en" => "RTA Card Back Is required"][app()->getLocale()],
            "vehicle_registration_form.required" => ["ar" => "استمارة تسجيل المركبه مطلوبة" , "en" => "Vehicle Registration Form Is required"][app()->getLocale()],
            'mobile_code.required' =>  ['ar' => 'كود الموبايل مطلوب' , 'en' => 'Mobile Code is required'][app()->getLocale()],
            'mobile_code.regex' => ['ar' => 'الكود الموبايل خاطئ' , 'en' => 'Mobile Code Format is Wrong'][app()->getLocale()]
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
