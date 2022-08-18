<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
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
            'issued_at'=>'sometimes|date',
            'type'=>'required|integer',
            'sender_party_id'=>'required|exists:parties,id',
            'sender_id_no'=>'nullable',
            'sender_phone'=>'nullable',
            'sender_address'=>'nullable',
            'sender_notes'=>'nullable',
            'receiver_party_id'=>'required|exists:parties,id',
            'receiver_id_no'=>'nullable',
            'receiver_phone'=>'nullable',
            'receiver_address'=>'nullable',
            'receiver_notes'=>'nullable',
            'receiver_country_id'=>'sometimes|exists:countries,id',
            'receiver_city_id'=>'sometimes|exists:cities,id',
            'commision_side'=>'sometimes|integer',
            'commision'=>'sometimes|numeric',
            'is_commision_percentage'=>'sometimes|boolean',
            'received_amount'=>'required|numeric',
            'to_send_amount'=>'required|numeric',
            'received_currency_id'=>'required|exists:currencies,id',
            'delivery_currency_id'=>'required|exists:currencies,id',
            'reference_currency_id'=>'required|exists:currencies,id',
            'exchange_rate_to_reference_currency'=>'required|numeric',
            'exchange_rate_to_delivery_currency'=>'required|numeric',
            'other_amounts_on_receiver'=>'sometimes|numeric',
            'other_amounts_on_sender'=>'sometimes|numeric',
            'office_id'=>'required|exists:parties,id',
            'office_currency_id'=>'required|exists:currencies,id',
            'office_commision'=>'sometimes|numeric',
            'office_commision_type'=>'sometimes|integer',
            'returned_commision'=>'sometimes|numeric'
        ];
    }
}
