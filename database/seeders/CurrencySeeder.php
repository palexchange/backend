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
        $currencies = [
            ['name' => 'دولار', 'weight' => 10],
            ['name' => 'شيكل', 'weight' => 8],
            ['name' => 'دينار', 'weight' => 9],
            ['name' =>  'يورو', 'weight' => 11],
            ['name' => 'درهم', 'weight' => 6],
            ['name' => 'ر.س', 'weight' => 7],
            ['name' => 'جنيه', 'weight' => 1],
        ];
        // $currencies= ['USD','JOD','EGP'];
        foreach ($currencies as $currency) {
            // $c_account = Account::create([
            //     'name' => $currency,
            //     'type_id' => 3
            // ]);
            $c = Currency::create([
                'name' => $currency['name'],
                'weight' => $currency['weight'],
            ]);
        }
    }
}
