<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock extends BaseModel
{
    use HasFactory;
    protected $appends = ['name',  'mid', 'close_mid'];
    // protected $appends = ['currency_name', 'ref_currency_name', 'mid', 'close_mid'];
    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }
    public function ref_currency()
    {
        return $this->hasOne(Currency::class, 'id', 'ref_currency_id');
    }
    public function getNameAttribute()
    {
        return $this->currency()->get('currencies.name')->first()->name;
    }
    public function getMidAttribute()
    {
        $id = $this->id;
        return DB::select("select (start_selling_price::numeric + start_purchasing_price::numeric)/2  as mid from stocks where id = $id ")[0]->mid;
    }
    public function getCloseMidAttribute()
    {
        $id = $this->id;
        return DB::select("select (final_selling_price::numeric + final_purchasing_price::numeric)/2  as mid from stocks where id = $id ")[0]->mid;
    }
    public function getRefCurrencyNameAttribute()
    {
        return $this->ref_currency->name;
    }

    public function scopeSort($query, $request)
    {
        $sortBy = $request->sortBy;
        $sortDesc = $request->sortDesc;
        $custom_fields = [];
        if (!$sortBy && !$sortDesc)
            $query->orderby('id', 'asc');
        if ($sortBy && $sortDesc) {
            foreach ($sortBy as $index => $field) {
                $desc = $sortDesc[$index] == 'true' ? "desc" : "asc";
                if (!isset($custom_fields[$field])) {
                    $query->orderBy($field, $desc);
                } else {
                    $custom_fields[$field]($query, $desc);
                }
            }
        }
    }
}
