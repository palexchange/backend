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

        $query
            ->where(DB::raw("date_part('month',time::timestamp) "), DB::raw("date_part('month',CURRENT_DATE) "))
            ->select(
                'stock_id',
                DB::raw('round(((selling_price::numeric+ purchasing_price::numeric) /2),5)as mid'),
                DB::raw("date_part('day',time::timestamp) as day"),
            );
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
