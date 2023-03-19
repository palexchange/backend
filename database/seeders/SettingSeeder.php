<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Party;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $moneygram_account = Account::create(['name' => 'موني غرام', 'type_id' => 2]);
        $mony_gram_party = Party::create(['name' => 'موني غرام', 'account_id' => $moneygram_account->id]);
        //
        // $losses_and_profits =         Account::create(['name' => 'أرباح وخسائر', 'type_id' => 6]);
        // $commssion_account = Account::create(['name' => 'Commission Account', 'type_id' => 1]);
        // $returned_commission_account = Account::create(['name' => 'Returned Commission Account', 'type_id' => 1]);
        // $transfer_expense_account = Account::create(['name' => 'Transfer Expense Account', 'type_id' => 1]);
        // $exchange_difference_account = Account::create(['name' => 'Exchange Difference Account', 'type_id' => 1]);
        // $commssion_account = Account::create(['name' => 'Commission Account', 'type_id' => 1]);
        // $transfer_commission_account = Account::create(['name' => 'Transfer Commission Account', 'type_id' => 1]);
        // $returned_commission_account = Account::create(['name' => 'Returned Commission Account', 'type_id' => 1]);
        // $transfer_expense_account = Account::create(['name' => 'Transfer Expense Account', 'type_id' => 1]);
        $exchange_difference_account = Account::create(['name' => 'فرق عملة', 'type_id' => 5]);
        $returned_commission_account = Account::create(['name' => 'مرجع', 'type_id' => 5]);
        $office_commission_account = Account::create(['name' => 'عمولة وسيط', 'type_id' => 5]);
        $transfers_commission_account = Account::create(['name' => 'عمولة حوالات', 'type_id' => 5]);
        $settings = [
            // ['key' => 'commission_account_id', 'value' => $commssion_account->id, 'description' => 'transfer commission account'],
            // ['key' => 'transfer_commission_account_id', 'value' => $transfer_commission_account->id,  'description' => 'transfer commission account'],
            // ['key' => 'returned_commission_account_id', 'value' => $returned_commission_account->id, 'description' => 'transfer returned commission account'],
            ['key' => 'exchange_difference_account_id', 'value' => $exchange_difference_account->id, 'description' => 'exchange difference account'],
            // ['key' => 'financial_year_end', 'value' => $financial_year_end],
            // ['key' => 'digits_number', 'value' => 2],
            // ['key' => 'time_allowed_for_deletion', 'value' => 3, 'description' => 'the allowed time to delete'],
            ['key' => 'time_allowed_for_deletion', 'value' => 3, 'description' => 'the allowed time to delete'],
            ['key' => 'general_customer', 'value' => 1, 'description' => 'general customer account'],
            ['key' => 'moneygram_account', 'value' => $mony_gram_party->id, 'description' => 'moneygram account'],
            ['key' => 'returned_commission_account_id', 'value' => $returned_commission_account->id, 'description' => 'returned_commission_account'],
            ['key' => 'office_commission_account_id', 'value' => $office_commission_account->id, 'description' => 'office_commission_account'],
            ['key' => 'transfers_commission_account_id', 'value' => $transfers_commission_account->id, 'description' => 'transfer_commission_account'],
            ['key' => 'days_to_search_before', 'value' => 14, 'description' => 'default value to search before'],
            // ['key' => 'losses_and_profits', 'value' => $losses_and_profits->id, 'description' => 'losses and profits account'],
        ];
        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
