<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Setting;
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
        $commssion_account = Account::create(['name' => 'Commission Account', 'type_id' => 1]);
        $transfer_commission_account = Account::create(['name' => 'Transfer Commission Account', 'type_id' => 1]);
        $returned_commission_account = Account::create(['name' => 'Returned Commission Account', 'type_id' => 1]);
        $transfer_expense_account = Account::create(['name' => 'Transfer Expense Account', 'type_id' => 1]);
        $exchange_difference_account = Account::create(['name' => 'Exchange Difference Account', 'type_id' => 1]);
        $settings = [
            ['key' => 'commission_account_id', 'value' => $commssion_account->id],
            ['key' => 'transfer_commission_account_id', 'value' => $transfer_commission_account->id],
            ['key' => 'transfer_expense_account_id', 'value' => $transfer_expense_account->id],
            ['key' => 'returned_commission_account_id', 'value' => $returned_commission_account->id],
            ['key' => 'exchange_difference_account_id', 'value' => $exchange_difference_account->id],
            ['key' => 'digits_number', 'value' => 2],
        ];
        foreach ($settings as $setting) {
            Setting::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
            ]);
        }
    }
}
