<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
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
            'statement' => 'sometimes',
            'status' => 'required|in:0,1,128,255',
            'document_type' => 'sometimes|integer',
            'document_sub_type' => 'sometimes|integer',
            'user_id' => 'sometimes|exists:users,id',
            'document_id' => 'sometimes|integer'
        ];
    }
}
