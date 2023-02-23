<?php

namespace App\Listeners;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class SetStockListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {

        $today = Carbon::today()->toDateString();
        $stocks = Stock::all(); // DB::select('select * from stocks ');
        foreach ($stocks as $stock) {
            if ($stock->closed_at && $today > Carbon::createFromFormat('Y-m-d H:i:s', $stock->closed_at)->format('Y-m-d')) {
                $stock->update([
                    'start_selling_price' => $stock->final_selling_price,
                    'start_purchasing_price' => $stock->final_purchasing_price,
                    'closed_at' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
                ]);
            }
        }
    }
}
