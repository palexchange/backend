<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeDetail extends BaseModel
{
    use HasFactory;
    protected $appends = ["currency_name"];

    // public function currency()
    // {
    //     return $this->hasOne(Currency::class,"id","currency_id");
    // }
    public function getCurrencyNameAttribute()
    {
        return $this->currency()->first("name")->name;
    }

    public function scopeSearch($query, $request)
    {
        $query->when($request->exchange_id, function ($query, $exchange_id) {
            $query->where("exchange_id", $exchange_id);
        });
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function log(Entry $entry)
    {
        return EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' =>  $this->currency->account_id,
            'creditor' => $this->amount,
            'debtor' => 0,
            'ac_creditor' => $this->amount_after,
            'ac_debtor' => 0,
            'exchange_rate' => $this->factor
        ]);
    }
    public function getUserAccountIdAttribute()
    {
        $user = User::find(2);
        return $user->accounts()->where('status', 1)->where('currency_id', $this->currency()->first("id")->id)->first(['accounts.id'])->id;
    }
}
