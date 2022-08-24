<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockTransactionRequest extends FormRequest
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
            'stock_id'=>'required|exists:stocks,id',
            'selling_price'=>'required|numeric',
            'purchasing_price'=>'required|numeric',
            'time'=>'required|timestamp'
        ];
    }
}
