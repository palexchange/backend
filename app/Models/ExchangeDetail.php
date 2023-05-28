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
        $out = $this->type ==  2;
        $usd_amount = $this->amount / $this->usd_factor;
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' =>  $this->user_account_id,
            'currency_id' =>  $this->currency_id,
            'creditor' => $out ?  $this->amount : 0,
            'debtor' => !$out ?  $this->amount : 0,
            'transaction_type' => $out ?   17 : 18,
            'ac_creditor' => $out ?   $usd_amount : 0,
            'ac_debtor' => !$out ?   $usd_amount : 0,
            'exchange_rate' => $this->usd_factor
        ]);
    }

    public function getUserAccountIdAttribute()
    {
        $user = auth()->user();
        return $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', $this->currency()->first("id")->id)
            ->first(['accounts.id'])->id;
    }
}
