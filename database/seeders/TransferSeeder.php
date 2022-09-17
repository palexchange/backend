<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Party;
use App\Models\Transfer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $sender_account = Account::create([
            'name' => 'Ahmad Rafee',
            'type_id' => 1,
        ]);
        $sender = Party::create([
            'name' => 'Ahmad Rafee',
            'id_no' => '411578254',
            'phone' => '0597857597',
            'address' => 'Alsekka St.',
            'account_id' => $sender_account->id,
        ]);
        $receiver_account = Account::create([
            'name' => 'Mohammed Eslim',
            'type_id' => 1,
        ]);
        $receiver = Party::create([
            'name' => 'Mohammed Eslim',
            'id_no' => '411254778',
            'phone' => '0597857597',
            'address' => 'West Gaza - Alshate Camp',
            'account_id' => $receiver_account->id,
        ]);
        $office_account = Account::create([
            'name' => 'Thabet Office',
            'type_id' => 1,
        ]);
        $office = Party::create([
            'name' => 'Thabet Office',
            'id_no' => '411251438',
            'phone' => '0597857597',
            'address' => 'Altrans St.',
            'account_id' => $office_account->id,
        ]);

        $transfer1 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'transfer_commission' => 10,
            'received_amount' => 100,
            'a_received_amount'=>100,
            'to_send_amount' => 100,
            'received_currency_id' => 1,
            'delivery_currency_id' => 1,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 1,
            'exchange_rate_to_delivery_currency' => 1,
            'office_id' => $office->id,
            'office_currency_id' => 1,
            'office_commission' => 5,
            'exchange_rate_to_office_currency' => 1,
            'office_amount' => 105,
            'final_received_amount' => 110
        ]);
        $transfer2 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'transfer_commission' => 10,
            'received_amount' => 100,
            'a_received_amount'=>100,
            'to_send_amount' => 100,
            'other_amounts_on_sender'=>2,
            'received_currency_id' => 1,
            'delivery_currency_id' => 1,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 1,
            'exchange_rate_to_delivery_currency' => 1,
            'office_id' => $office->id,
            'office_currency_id' => 1,
            'office_commission' => 5,
            'exchange_rate_to_office_currency' => 1,
            'office_amount' => 105,
            'final_received_amount' => 112
        ]);
        $transfer3 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'received_amount' => 100,
            'a_received_amount'=>100,
            'transfer_commission' => 10,
            'final_received_amount' => 110,
            'to_send_amount' => 100,
            'other_amounts_on_receiver'=>2,
            'received_currency_id' => 1,
            'delivery_currency_id' => 1,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 1,
            'exchange_rate_to_delivery_currency' => 1,
            'office_id' => $office->id,
            'office_currency_id' => 1,
            'exchange_rate_to_office_currency' => 1,
            'office_amount' => 93,
            'returned_commission'=>5,
        ]);
        $transfer4 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'transfer_commission' => 10,
            'received_amount' => 1000,
            'a_received_amount'=>1000,
            'final_received_amount' => 1010,
            'to_send_amount' => 1000,
            'received_currency_id' => 1,
            'delivery_currency_id' => 5,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 1,
            'exchange_rate_to_delivery_currency' => 3.6,
            'office_id' => $office->id,
            'office_currency_id' => 2,
            'office_amount' => 986.74,
            'exchange_rate_to_office_currency'=>3.63,
            'returned_commission'=>5,
        ]);
        $transfer5 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'transfer_commission' => 10,
            'received_amount' => 3200,
            'a_received_amount'=>993.79,
            'final_received_amount' => 1003.79,
            'to_send_amount' => 1000,
            'received_currency_id' => 2,
            'delivery_currency_id' => 3,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 3.22,
            'exchange_rate_to_delivery_currency' => 0.69,
            'office_id' => $office->id,
            'office_currency_id' => 1,
            'exchange_rate_to_office_currency'=>3.63,
            'returned_commission'=>5,
            'office_amount' =>  974.59,
        ]);
        $transfer6 = Transfer::create([
            'status' => 1,
            'sender_party_id' => $sender->id,
            'receiver_party_id' => $receiver->id,
            'received_amount' => 100,
            'transfer_commission' => 1,
            'other_amounts_on_sender'=>5,
            'final_received_amount' => 115,
            'a_received_amount'=>100,
            'to_send_amount' => 100,
            'received_currency_id' => 1,
            'delivery_currency_id' => 1,
            'reference_currency_id' => 1,
            'exchange_rate_to_reference_currency' => 1,
            'exchange_rate_to_delivery_currency' => 1,
            'office_id' => $office->id,
            'office_currency_id' => 1,
            'office_amount' => 100,
            'exchange_rate_to_office_currency'=>1,
            'office_commission' => 9,
            'office_amount' => 109,
        ]);
        $transfer1->confirm();
        $transfer2->confirm();
        $transfer3->confirm();
        $transfer4->confirm();
        $transfer5->confirm();
        $transfer6->confirm();
    }
}
