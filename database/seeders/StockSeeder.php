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
        $test  = [
            [1, 1, 1],
            [2, 3.78, 3.78],
            [3, 0.71, 0.71],
            [4, 1.08, 1.08],
            [5, 3.83783783783, 3.83783783783],
            [6, 3.83783783783, 3.83783783783],
            [7, 32.8695652173, 32.8695652173]
        ];
        foreach ($test as  $stock) {
            Stock::create([
                'currency_id' => $stock[0],
                'ref_currency_id' => 1,
                'start_selling_price' => $stock[1],
                'start_purchasing_price' => $stock[1],
                'final_selling_price' => $stock[1],
                'final_purchasing_price' => $stock[1],
                'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
            ]);
        }
    }
}
