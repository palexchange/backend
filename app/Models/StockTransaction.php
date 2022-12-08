<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends BaseModel
{
    use HasFactory;
    public  $appends  = ['mid'];
    public  $with  = ['stock'];
    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
        $query->where('closing', true)->orderBy('id',  'DESC');
    }
    public function getMidAttribute()
    {
        return ($this->purchasing_price + $this->selling_price) / 2;
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
