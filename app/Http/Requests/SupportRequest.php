<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BetweenOneAndFive;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\System\System;

class SupportRequest extends FormRequest
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

    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
           'name' => 'regex:/^[a-zA-Z]+$/',
            'email' => ['email'],
            'mobile' => ['regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'],
            'mobile_code' => 'regex:/^\+\d{1,4}$/',
            'description' => ['required', 'regex:/^[\p{Arabic}\p{Latin}\s0-9]+$/u'],
        ];
    }

    public function messages()
    {
        return [
            "name.regex" => ["ar" => "name is incorrect" , "en" => "الاسم خاطئ"][app()->getLocale()],
            "email.regex" => ["ar" => "Email is incorrect" , "en" => "البريد خاطئ"][app()->getLocale()],
            "mobile.regex" => ["ar" => "mobile is incorrect" , "en" => "رقم الهاتف خاطئ"][app()->getLocale()],
            "mobile_code.regex" => ["ar" => "Mobile Code is incorrect" , "en" => "كود البلد خاطئ"][app()->getLocale()],
            "description.regex" => ["ar" => "Description is incorrect" , "en" => "الوصف خاطئ"][app()->getLocale()],
            "description.required" => ["ar" => "Description is required" , "en" => "الوصف مطلوب"][app()->getLocale()]

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success([] , System::HHTP_Unprocessable_Content , $message );
        throw new HttpResponseException($response);
    }
}
