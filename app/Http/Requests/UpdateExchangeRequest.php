<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRequest extends FormRequest
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
            'date'=>'sometimes|date',
            'amount'=>'sometimes|numeric',
            'currency_id'=>'sometimes|exists:currencies,id',
            'beneficiary_id'=>'sometimes|exists:parties,id',
            'number'=>'nullable',
        ];
    }
}
