<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends BaseModel
{
    use HasFactory;
    protected $with = ['currency'];

    public function currency()
    {
        return $this->belongsTo(Currency::class)->withDefault(['name' => 'must assign a currency', 'id' => 999]);
    }


    public function scopeSearch($query, $request)
    {
        $query->when($request->type_id, function ($query, $type_id) {
            $query->where("type_id", $type_id);
        });
    }
}
