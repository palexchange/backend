<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeDetailRequest extends FormRequest
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
            'exhange_id'=>'required|exists:exchanges,id',
            'amount'=>'sometimes|numeric',
            'currency_id'=>'sometimes|exists:currencies,id',
            'factor'=>'sometimes|integer',
            'amount_after'=>'sometimes|integer'
        ];
    }
}
