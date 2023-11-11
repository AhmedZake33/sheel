<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardRequest extends FormRequest
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
            'card_number' => 'required',
            'cvc' => 'required',
            'exp_month' => 'required',
            'exp_year' => 'required',
            'name' => 'required',
            'region' => 'required'
        ];
    }

}
