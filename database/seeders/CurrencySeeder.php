<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = ['دولار', 'شيكل', 'دينار', 'يورو', 'درهم', 'ر.س', 'جنيه'];
        // $currencies= ['USD','JOD','EGP'];
        foreach ($currencies as $currency) {
            // $c_account = Account::create([
            //     'name' => $currency,
            //     'type_id' => 3
            // ]);
            $c = Currency::create([
                'name' => $currency,
            ]);
        }
    }
}
