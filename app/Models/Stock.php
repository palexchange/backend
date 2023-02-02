<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock extends BaseModel
{
    use HasFactory;
    protected $appends = ['currency_name', 'ref_currency_name', 'mid'];
    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }
    public function ref_currency()
    {
        return $this->hasOne(Currency::class, 'id', 'ref_currency_id');
    }
    public function getCurrencyNameAttribute()
    {
        return $this->currency->name;
    }
    public function getMidAttribute()
    {
        $id = $this->id;
        return DB::select("select (start_selling_price::numeric + start_purchasing_price::numeric)/2  as mid from stocks where id = $id ")[0]->mid;
    }
    public function getRefCurrencyNameAttribute()
    {
        return $this->ref_currency->name;
    }
    public function scopeSort($query, $request)
    {
    }
}
