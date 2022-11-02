<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAccountRequest extends FormRequest
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
            'user_id' => 'sometimes|exists:users,id',
            'account_id' => 'sometimes|exists:accounts,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'sometimes|numeric',
            'main' => 'sometimes|boolean',
        ];
    }
}
