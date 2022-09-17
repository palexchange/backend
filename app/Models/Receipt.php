<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends BaseModel
{
    use HasFactory;

    protected $appends =["party_name","currency"];
    
    public function party()
    {
        return $this->hasOne(Party::class,"id","beneficiary_id");
    }

    public function currency()
    {
        return $this->hasOne(Currency::class,"id","currency_id");
    }

    public function getPartyNameAttribute()
    {
        return $this->party()->first("name")->name;
    }

    public function getCurrencyAttribute()
    {
        return $this->currency()->first("name")->name;
    }

    public function scopeSearch($query, $request)
    {
        $query->when($request->type, function ($query, $type) {
            $query->where("type",$type);
        });
    }
}
