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

        Account::create(['name' => 'ربحية الحوالات', 'type_id' => 8, 'currency_id' => 1]); // 2
        Account::create(['name' => 'ربحية الصرافة', 'type_id' => 8, 'currency_id' => 1]); // 3

        Account::create(['name' => 'خزينة دولار', 'type_id' => 4,  'currency_id' => 1]); // 4
        Account::create(['name' => 'خزينة شيكل', 'type_id' => 4,  'currency_id' => 2]); // 5
        Account::create(['name' => 'خزينة دينار', 'type_id' => 4,  'currency_id' => 3]); // 6
        Account::create(['name' => 'خزينة يورو', 'type_id' => 4,  'currency_id' => 4]);
        Account::create(['name' => 'خزينة درهم', 'type_id' => 4,  'currency_id' => 5]);
        Account::create(['name' => 'خزينة ر.س', 'type_id' => 4,  'currency_id' => 6]);
        Account::create(['name' => 'خزينة جنيه', 'type_id' => 4,  'currency_id' => 7]);

        Account::create(['name' => 'ص.دولار كاظم', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'ص.شيكل كاظم', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'ص.دينار كاظم', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'ص.يورو كاظم', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'ص.درهم كاظم', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'ص.ر.س كاظم', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'ص.جنيه كاظم', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'ص.دولار محمد', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'ص.شيكل محمد', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'ص.دينار محمد', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'ص.يورو محمد', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'ص.درهم محمد', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'ص.ر.س محمد', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'ص.جنيه محمد', 'type_id' => 3, 'currency_id' => 7]);

        Account::create(['name' => 'ص.دولار احمد', 'type_id' => 3, 'currency_id' => 1]);
        Account::create(['name' => 'ص.شيكل احمد', 'type_id' => 3, 'currency_id' => 2]);
        Account::create(['name' => 'ص.دينار احمد', 'type_id' => 3, 'currency_id' => 3]);
        Account::create(['name' => 'ص.يورو احمد', 'type_id' => 3, 'currency_id' => 4]);
        Account::create(['name' => 'ص.درهم احمد', 'type_id' => 3, 'currency_id' => 5]);
        Account::create(['name' => 'ص.ر.س احمد', 'type_id' => 3, 'currency_id' => 6]);
        Account::create(['name' => 'ص.جنيه احمد', 'type_id' => 3, 'currency_id' => 7]);



        Account::create(['name' => 'حساب رأس المال', 'type_id' => 4, 'currency_id' => 1, 'is_transaction' => false]);
        Account::create(['name' => 'ريحية العجز و الزيادة', 'type_id' => 8, 'currency_id' => 1]); // 2
        Account::create(['name' => 'أرباح وخسائر', 'type_id' => 8, 'currency_id' => 1]); // 2


        $acc = Account::create(['name' => 'حساب المصروفات', 'type_id' => 7, 'currency_id' => 1]);
        Account::create(['name' => 'م.كهرباء', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.بلدية', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.ضريبة', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.ضيافة', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.مولد', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.قرطاسية', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.مواصلات', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.إيجار', 'type_id' => 7, 'currency_id' => 2, 'parent_id' => $acc->id]);
        Account::create(['name' => 'م.عمولات بنكية', 'type_id' => 7, 'parent_id' => $acc->id]);
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
