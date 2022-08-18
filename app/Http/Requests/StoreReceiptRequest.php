<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
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
            'main_currency_id'=>'required|exists:currencies,id',
            'currency_id'=>'required|exists:currencies,id',
            'factor'=>'sometimes|numeric',
            'beneficiary_id'=>'required|exists:parties,id',
            'number'=>'nullable',

        ];
    }
}
