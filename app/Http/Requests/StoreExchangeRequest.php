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
            'date' => 'required|date',
            'started_at' => 'required|date',
            // 'amount' => 'required|numeric',
            'profit' => 'required|numeric',
            // 'currency_id' => 'required|exists:currencies,id',
            'beneficiary_id' => 'nullable|exists:parties,id',
            // 'number' => 'nullable|numeric',
            // 'reference_currency_id' => 'required|exists:currencies,id',
            'status' => 'nullable|numeric',
            // 'exchange_rate' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            // 'amount_after' => 'nullable|numeric',


            'items.*.amount' => 'required|numeric',
            'items.*.currency_id' => 'required|exists:currencies,id',
            'items.*.exchange_rate' => 'sometimes|numeric',
            'items.*.type' => 'sometimes|numeric',
            'items.*.usd_factor' => 'sometimes|numeric'

        ];
    }
}
