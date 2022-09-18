<?php

namespace Database\Seeders;

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
        $currencies = ['دولار', 'شيكل', 'دينار', 'يورو', 'درهم', 'ريال سعودي'];
        foreach ($currencies as  $value) {
            Currency::create(['name' => $value]);
        }
    }
}
