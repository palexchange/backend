<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreStockRequest extends FormRequest
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
    public function rules(Request $request)
    {

        return [
            //
            '*.date' => 'sometimes|date',
            '*.opened_at' => 'nullable|date_format:Y-m-d H:i:s',
            '*.closed_at' => 'nullable|date_format:Y-m-d H:i:s',
            '*.start_selling_price' => 'sometimes|numeric',
            '*.final_selling_price' => 'sometimes|numeric',
            '*.start_purchasing_price' => 'sometimes|numeric',
            '*.final_purchasing_price' => 'sometimes|numeric',
            '*.ref_currency_id' => 'sometimes|exists:currencies,id',
            '*.currency_id' => 'sometimes|exists:currencies,id',
        ];
    }
}
