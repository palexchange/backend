<?php

namespace Database\Seeders;

use App\Models\Account;
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
        //
        // $commssion_account = Account::create(['name' => 'Commission Account', 'type_id' => 1]);
        $transfer_commission_account = Account::create(['name' => 'Transfer Commission Account', 'type_id' => 1]);
        // $returned_commission_account = Account::create(['name' => 'Returned Commission Account', 'type_id' => 1]);
        // $transfer_expense_account = Account::create(['name' => 'Transfer Expense Account', 'type_id' => 1]);
        // $exchange_difference_account = Account::create(['name' => 'Exchange Difference Account', 'type_id' => 1]);
        // $financial_year_end = Carbon::createMidnightDate(2023, 8, 30);
        $settings = [
            // ['key' => 'commission_account_id', 'value' => $commssion_account->id],
            // ['key' => 'transfer_commission_account_id', 'value' => $transfer_commission_account->id,  'description' => 'transfer commission account'],
            // ['key' => 'transfer_expense_account_id', 'value' => $transfer_expense_account->id],
            // ['key' => 'returned_commission_account_id', 'value' => $returned_commission_account->id],
            // ['key' => 'exchange_difference_account_id', 'value' => $exchange_difference_account->id],
            // ['key' => 'financial_year_end', 'value' => $financial_year_end],
            // ['key' => 'digits_number', 'value' => 2],
            // ['key' => 'time_allowed_for_deletion', 'value' => 3, 'description' => 'the allowed time to delete'],
            ['key' => 'time_allowed_for_deletion', 'value' => 3, 'description' => 'the allowed time to delete'],
            ['key' => 'general_customer', 'value' => 1, 'description' => 'general customer account'],
            ['key' => 'moneygram_account', 'value' => 2, 'description' => 'moneygram account'],
        ];
        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
