<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyRequest extends FormRequest
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
            'name'=>'required',
            'id_no'=>'nullable',
            'phone'=>'nullable',
            'address'=>'nullable',
            'country_id'=>'sometimes|exists:countries,id',
            'city_id'=>'sometimes|exists:cities,id',
            'type'=>'sometimes|in:0,1'
        ];
    }
}
