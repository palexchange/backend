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
            'type' => 'required|integer',
            'issued_at' => 'sometimes|date',
            'status' => 'sometimes',
            'number' => 'sometimes',
            'final_received_amount' => 'required',
            'delivering_type' => 'required|integer',
            'sender_party_id' => 'required|exists:parties,id',
            'sender_id_no' => 'nullable',
            'sender_phone' => 'nullable',
            'sender_address' => 'nullable',
            'sender_notes' => 'nullable',
            'receiver_party_id' => 'required|exists:parties,id',
            'receiver_id_no' => 'nullable',
            'receiver_phone' => 'nullable',
            'receiver_address' => 'nullable',
            'receiver_notes' => 'nullable',
            'receiver_country_id' => 'nullable|exists:countries,id',
            'receiver_city_id' => 'nullable|exists:cities,id',
            'commission_side' => 'sometimes|integer',
            'commission' => 'sometimes|numeric',
            'is_commission_percentage' => 'sometimes|boolean',
            'received_amount' => 'required|numeric',
            'to_send_amount' => 'required|numeric',
            'received_currency_id' => 'required|exists:currencies,id',
            'delivery_currency_id' => 'required|exists:currencies,id',
            'reference_currency_id' => 'required|exists:currencies,id',
            'exchange_rate_to_reference_currency' => 'required|numeric',
            'exchange_rate_to_delivery_currency' => 'required|numeric',
            'other_amounts_on_receiver' => 'sometimes|numeric',
            'other_amounts_on_sender' => 'sometimes|numeric',
            'office_id' => 'required|exists:parties,id',
            'office_currency_id' => 'required|exists:currencies,id',
            'office_commission' => 'sometimes|numeric',
            'exchange_rate_to_office_currency' => 'sometimes|numeric',
            'office_commission_type' => 'sometimes|integer',
            'transfer_commission' => 'sometimes|numeric',
            'office_amount' => 'sometimes|numeric',
            'office_amount_in_office_currency' => 'sometimes|numeric',
            'a_received_amount' => 'sometimes|numeric',
            'user_id' => 'required|exists:users,id',
            'returned_commission' => 'sometimes|numeric'
        ];
    }
}
