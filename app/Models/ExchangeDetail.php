<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeDetail extends BaseModel
{
    use HasFactory;
    protected $appends =["currency"];

    public function currency()
    {
        return $this->hasOne(Currency::class,"id","currency_id");
    }

    public function getCurrencyAttribute()
    {
        return $this->currency()->first("name")->name;
    }
    
    public function scopeSearch($query, $request)
    {
        $query->when($request->exchange_id, function ($query, $exchange_id) {
            $query->where("exchange_id", $exchange_id);
        });
    }
}
