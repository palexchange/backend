<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'name' => 'required|unique:accounts,name',
            'parent_id' => 'sometimes|exists:accounts,id',
            'type_id' => 'required|exists:account_types,id',
            'code' => 'nullable',
            'description' => 'nullable',
            'notes' => 'nullable',
            'user_id' => 'sometimes|exists:users,id',
            'is_transaction' => 'sometimes|boolean'
        ];
    }
}
