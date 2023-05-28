<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryTransactionRequest extends FormRequest
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
            'entry_id' => 'required|exists:entries,id',
            'currency_id' => 'required|exists:currencies,id',
            'debtor' => 'required|numeric',
            'ac_debtor' => 'sometimes|numeric',
            'creditor' => 'required|numeric',
            'ac_creditor' => 'sometimes|numeric',
            'transaction_type' => 'sometimes|numeric',
            'exchange_rate' => 'sometimes|numeric',
            'account_id' => 'required|exists:accounts,id',
            'source_type' => 'sometimes|integer',
            'source_id' => 'sometimes|integer',
            'subject_type' => 'sometimes|integer',
            'subject_id' => 'sometimes|integer',
        ];
    }
}
