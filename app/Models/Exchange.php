<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends BaseModel implements Document
{
    // protected $with = ["ben"];
    protected $appends =["party_name","currency"];
    // protected $hidden =["ben"];
    
    use HasFactory;
    
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
    
    public function confirm()
    {
        
    }
    public function dispose()
    {
        
    }
}
