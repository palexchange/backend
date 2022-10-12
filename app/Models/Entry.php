<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends BaseModel
{
    use HasFactory;
    public function document()
    {
        return $this->morphTo();
    }
    public function ref_currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function scopeSort($query, $request)
    {
    }

}
