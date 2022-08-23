<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends BaseModel
{
    use HasFactory;
    protected $appends = ['currency_name', 'ref_currency_name'];
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
    public function getRefCurrencyNameAttribute()
    {
        return $this->ref_currency->name;
    }
}
