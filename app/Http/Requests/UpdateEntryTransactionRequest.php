<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryTransactionRequest extends FormRequest
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
            'entry_id'=>'sometimes|exists:entries,id',
            'debtor'=>'integer',
            'creditor'=>'integer',
            'account_id'=>'sometimes|exists:accounts,id',
            'source_type'=>'sometimes|integer',
            'source_id'=>'sometimes|integer',
            'subject_type'=>'sometimes|integer',
            'subject_id'=>'sometimes|integer',
        ];
    }
}
