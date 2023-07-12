<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\System\System;

class requestCreateRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'lat' => 'required|regex:/^[0-9 .]+$/',
            'lng' => 'required|regex:/^[0-9 .]+$/',
            'service_id' => 'required|exists:services,id'
        ];
    }
    
    public function messages()
    {
        return [
            'lat.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'lat.regex' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'lng.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'lng.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'service_id.required' => ['ar' => 'برجاء اختيار الخدمة المطلوبة ' , 'en' => 'Please Select Service'][$this->lang],
            'service_id.exists' => ['ar' => '  برجاء ادخال الخدمة بشكل صحيح  ' , 'en' => 'Please Select Correct Service'][$this->lang]


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
