<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends BaseModel
{
    use HasFactory;
    protected $with = ['currency'];
    protected $appends = ['balance'];
    protected $hidden = ['entry_transactions'];

    public function currency()
    {
        return $this->belongsTo(Currency::class)->withDefault(['name' => 'must assign a currency', 'id' => 999]);
    }
    public function entry_transactions()
    {
        return $this->hasMany(EntryTransaction::class, 'account_id', 'id');
    }
    public function getBalanceAttribute()
    {
        return $this->entry_transactions()->sum(DB::raw('debtor - creditor'));
    }


    public function scopeSearch($query, $request)
    {
        $query->when($request->type_id, function ($query, $type_id) {
            $query->where("type_id", $type_id);
        });
    }
}
