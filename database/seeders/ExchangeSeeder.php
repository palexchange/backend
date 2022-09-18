<?php

namespace Database\Seeders;

use App\Models\Exchange;
use App\Models\ExchangeDetail;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $exchange= Exchange::create([
            'date'=>Carbon::now(),
            'amount'=>1000,
            'currency_id'=>1,
            'beneficiary_id'=>1,
            'reference_currency_id'=>1,
            'exchange_rate'=>1,
            'amount_after'=>1000,
        ]);
        ExchangeDetail::create([
            'exchange_id'=>$exchange->id,
            'amount'=>1000,
            'currency_id'=>2,
            'factor'=>3.4,
            'amount_after'=>3400
        ]);
        $exchange->confirm();
    }
}
