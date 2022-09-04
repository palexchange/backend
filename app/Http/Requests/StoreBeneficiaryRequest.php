<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeneficiaryRequest extends FormRequest
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
            'name' => 'required',
            'mobile' => 'sometimes',
            'id_no' => 'sometimes',
            'address' => 'sometimes',
            'id_image' => 'sometimes|exists:files,id',
            'default_currency' => 'sometimes|exists:currencies,id'
        ];
    }
}
