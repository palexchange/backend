<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryRequest extends FormRequest
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
            'date'=>'sometimes|timestamp',
            'status'=>'sometimes|in:0,1,128,255',
            'document_type'=>'sometimes|integer',
            'document_id'=>'sometimes|integer'
        ];
    }
}
