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
            'from_account_id' => 'sometimes|exists:accounts,id',
            'to_account_id' => 'sometimes|exists:accounts,id',
            'from_amount' => 'nullable|numeric',
            'to_amount' => 'nullable|numeric',
            'status' => 'nullable|numeric',
            'type' => 'required|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'statement' => 'nullable',
            'user_id' => 'required|exists:users,id',
            'exchange_rate' => 'sometimes|numeric',
        ];
    }
}
