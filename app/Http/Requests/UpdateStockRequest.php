<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
            'date'=>'required|date',
            'opened_at'=>'sometimes|timestamp',
            'closed_at'=>'sometimes|timestamp',
            'start_selling_price'=>'sometimes|numeric',
            'final_selling_price'=>'sometimes|numeric',
            'start_purchasing_price'=>'sometimes|numeric',
            'final_purchasing_price'=>'sometimes|numeric',
            'currency_id'=>'required|exists:currencies,id',
            'main_currency_id'=>'required|exists:currencies,id',
        ];
    }
}
