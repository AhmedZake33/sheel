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
            'current_lat' => 'required|regex:/^[0-9 .]+$/',
            'current_lng' => 'required|regex:/^[0-9 .]+$/',
            'destination_lat' => 'required|regex:/^[0-9 .]+$/',
            'destination_lng' => 'required|regex:/^[0-9 .]+$/',
            'service_id' => 'required|exists:services,id',
            'description' => 'nullable|regex:/^[A-Za-z ]+$/',
            'file.*' => 'nullable|mimes:jpeg,jpg,png,gif|max:10000',
            'request_id' => "numeric"
        ];
    }
    
    public function messages()
    {
        return [
            'current_lat.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'current_lat.regex' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'current_lng.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'current_lng.regex' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'destination_lat.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'destination_lat.regex' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'destination_lng.required' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'destination_lng.regex' => ['ar' => 'حدث مشكلة في ايجاد الموقع' , 'en' => 'Error occurred when getting Location'][$this->lang],
            'service_id.required' => ['ar' => 'برجاء اختيار الخدمة المطلوبة ' , 'en' => 'Please Select Service'][$this->lang],
            'service_id.exists' => ['ar' => '  برجاء ادخال الخدمة بشكل صحيح  ' , 'en' => 'Please Select Correct Service'][$this->lang],
            'destination.regex' => ['ar' => 'برجاء ادخال الوصف بشكل صحيح' , 'en' => 'Description Format is Incorrect'][$this->lang],
            'file.mimes' => ['ar' => 'برجاء اختيار الصوره بشكل صحيح' , 'en' => 'File format is incorrect'][$this->lang],
            'file.max' => ['ar' => 'برجاء اختيار حجم الصوره  بشكل صحيح' , 'en' => 'File Size Not Allow'][$this->lang],


        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $message = ($this->lang == 'en')? 'The data entered is incorrect' : 'البيانات المدخلة غير صحيحة' ;
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success([] , System::HHTP_Unprocessable_Content , $message);
        
        throw new HttpResponseException($response);
    }
}
