<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            //
            'date'=>'required|date',
            'amount'=>'required|numeric',
            'currency_id'=>'required|exists:currencies,id',
            'beneficiary_id'=>'required|exists:parties,id',
            'number'=>'nullable',
        ];
    }
}
