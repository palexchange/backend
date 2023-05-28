<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAccountRequest extends FormRequest
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
            'items.*.user_id' => 'required|exists:users,id',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.currency_id' => 'nullable|exists:currencies,id',
            'items.*.name' => 'nullable',
            'items.*.status' => 'sometimes|numeric',
            'items.*.id' => 'nullable',
        ];
    }
}
