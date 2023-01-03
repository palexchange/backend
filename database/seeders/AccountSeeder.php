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
        Account::create(['name' => 'خزينة ر.س', 'type_id' => 4,  'currency_id' => 6]);
        Account::create(['name' => 'خزينة جنيه', 'type_id' => 4,  'currency_id' => 7]);

        Account::create(['name' => 'صندوق دولار كاظم', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق شيكل كاظم', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق دينار كاظم', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق يورو كاظم', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق درهم كاظم', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق ر.س كاظم', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق جنيه كاظم', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'صندوق دولار محمد', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق شيكل محمد', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق دينار محمد', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق يورو محمد', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق درهم محمد', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق ر.س محمد', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق جنيه محمد', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'صندوق أحمد دولار', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'صندوق أحمد شيكل', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'صندوق أحمد دينار', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'صندوق أحمد يورو', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'صندوق أحمد درهم', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'صندوق أحمد ر.س', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'صندوق أحمد جنيه', 'type_id' => 3, 'currency_id' => 7]);



        Account::create(['name' => 'حساب رأس المال', 'type_id' => 4, 'currency_id' => 1, 'is_transaction' => false]);
        Account::create(['name' => 'ريحية العجز و الزيادة', 'type_id' => 5, 'currency_id' => 1]); // 2
        Account::create(['name' => 'أرباح وخسائر', 'type_id' => 5, 'currency_id' => 1]); // 2


        $acc = Account::create(['name' => 'حساب المصروفات', 'type_id' => 7, 'currency_id' => 1]);
        Account::create(['name' => 'مصروف كهرب', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        // Account::create(['name' => '', 'type_id' => 7, 'currency_id' => 3, 'parent_id' => $acc->id]);
        // Account::create(['name' => '', 'type_id' => 7, 'currency_id' => 4, 'parent_id' => $acc->id]);
        // Account::create(['name' => '', 'type_id' => 7, 'currency_id' => 5, 'parent_id' => $acc->id]);
        // Account::create(['name' => '', 'type_id' => 7, 'currency_id' => 6, 'parent_id' => $acc->id]);
        // Account::create(['name' => '', 'type_id' => 7, 'currency_id' => 7, 'parent_id' => $acc->id]);

        // Account::create(['name' => 'محفظة دولار', 'type_id' => 7,  'currency_id' => 1]); // 7
        // Account::create(['name' => 'محفظة شيكل', 'type_id' => 7,  'currency_id' => 2]); // 5
        // Account::create(['name' => 'محفظة دينار', 'type_id' => 7,  'currency_id' => 3]); // 6
        // Account::create(['name' => 'محفظة يورو', 'type_id' => 7,  'currency_id' => 4]);
        // Account::create(['name' => 'محفظة درهم', 'type_id' => 7,  'currency_id' => 5]);
        // Account::create(['name' => 'محفظة ر.س', 'type_id' => 7,  'currency_id' => 6]);
        // Account::create(['name' => 'محفظة جنيه', 'type_id' => 7,  'currency_id' => 7]);

        // Account::create(['name' => 'ربحية الصرافة', 'type_id' => 5, 'currency_id' => 1]); // 3
    }
}
