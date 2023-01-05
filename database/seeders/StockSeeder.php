<?php

namespace Database\Seeders;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $test  = [1, 2, 3, 4, 5, 6, 7];
        foreach ($test as  $value) {
            Stock::create([
                'currency_id' => $value,
                'ref_currency_id' => 1,
                'start_selling_price' => 3.5,
                'start_purchasing_price' => 3.5,
                'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
            ]);
        }
    }
}
