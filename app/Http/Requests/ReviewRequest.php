<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BetweenOneAndFive;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\System\System;

class ReviewRequest extends FormRequest
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
            // 'rate' => 'required|numeric|between:1,5',
            'rate' => ['required',new BetweenOneAndFive],
            'comment' => 'nullable|regex:/^[a-zA-Z 0-9]+$/'
        ];
    }

    public function messages()
    {
        return [
            // 'rate.between' => ['ar' => 'التقيم لابد ان يكون بين رقمين 1 و 5' , 'en' => 'Rate Must be Between in 1 and 5'][app()->getLocale()]
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $message = array_values($validator->errors()->toArray())[0][0];
        $response = success([] , System::HHTP_Unprocessable_Content , $message );
        throw new HttpResponseException($response);
    }
}
