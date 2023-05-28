<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransaction extends BaseModel
{
    use HasFactory;

    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
 
        $query->select(DB::raw('DISTINCT   stock_id'),  'mid', 'at_date')
            ->fromSub(function ($qq) {
                $qq->from('stock_transactions')
                    ->select('time', 'stock_id', DB::raw('round(((selling_price::numeric + purchasing_price::numeric) /2),5)as mid'),   DB::raw('time::date as at_date'));
            }, 'agg_tabel')
            ->where('time', DB::raw('(select max(time) from stock_transactions  where time::date = at_date )'))
            ->where(DB::raw("date_part('month',time::timestamp) "), DB::raw("date_part('month',CURRENT_DATE) "));
    }
    // public function getMidAttribute()
    // {
    //     return ($this->purchasing_price + $this->selling_price) / 2;
    // }
    // public function stock()
    // {
    //     return $this->belongsTo(Stock::class);
    // }
}
