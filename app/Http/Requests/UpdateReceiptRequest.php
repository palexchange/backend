<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReceiptRequest extends FormRequest
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
            'from_account_id' => 'sometimes|exists:accounts,id',
            'to_account_id' => 'sometimes|exists:accounts,id',
            'from_amount' => 'nullable|numeric',
            'to_amount' => 'nullable|numeric',
            'status' => 'nullable|numeric',
            'type' => 'sometimes|numeric',
            'exchange_rate' => 'sometimes|numeric',
        ];
    }
}
