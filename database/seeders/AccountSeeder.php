<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Account::create(['name' => 'حساب الخزينة المركزي', 'type_id' => 4, 'currency_id' => 1, 'is_transaction' => false]); // 1

        Account::create(['name' => 'ربحية الحوالات', 'type_id' => 5, 'currency_id' => 1]); // 2
        Account::create(['name' => 'ربحية الصرافة', 'type_id' => 5, 'currency_id' => 1]); // 3

        Account::create(['name' => 'خزينة دولار', 'type_id' => 4,  'currency_id' => 1]); // 4
        Account::create(['name' => 'خزينة شيكل', 'type_id' => 4,  'currency_id' => 2]); // 5
        Account::create(['name' => 'خزينة دينار', 'type_id' => 4,  'currency_id' => 3]); // 6
        Account::create(['name' => 'خزينة يورو', 'type_id' => 4,  'currency_id' => 4]);
        Account::create(['name' => 'خزينة درهم', 'type_id' => 4,  'currency_id' => 5]);
        Account::create(['name' => 'خزينة ريال سعودي', 'type_id' => 4,  'currency_id' => 6]);
        Account::create(['name' => 'خزينة جنيه', 'type_id' => 4,  'currency_id' => 7]);

        Account::create(['name' => 'صندوق دولار كاظم', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق شيكل كاظم', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق دينار كاظم', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق يورو كاظم', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق درهم كاظم', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق ريال سعودي كاظم', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق جنيه كاظم', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'صندوق دولار محمد', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق شيكل محمد', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق دينار محمد', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق يورو محمد', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق درهم محمد', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق ريال سعودي محمد', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق جنيه محمد', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'صندوق تيلر 3 دولار', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق تيلر 3 شيكل', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق تيلر 3 دينار', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق تيلر 3 يورو', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق تيلر 3 درهم', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق تيلر 3 ريال سعودي', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق تيلر 3 جنيه', 'type_id' => 3, 'currency_id' => 7]);



        Account::create(['name' => 'حساب رأس المال', 'type_id' => 4, 'currency_id' => 1, 'is_transaction' => false]);
        Account::create(['name' => 'ريحية العجز و الزيادة', 'type_id' => 5, 'currency_id' => 1]); // 2
        // Account::create(['name' => 'ربحية الصرافة', 'type_id' => 5, 'currency_id' => 1]); // 3
    }
}
