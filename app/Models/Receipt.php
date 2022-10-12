<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receipt extends BaseModel implements Document
{
    use HasFactory;
    protected $appends = ['from_account_name', 'to_account_name'];
    public function confirm()
    {
        if ($this->type == 3) {
            $this->log_fund_adjustment();
        }
    }
    public function dispose()
    {
    }
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }
    public function from_account()
    {
        return $this->belongsTo(Account::class, 'from_account_id', 'id');
    }
    public function to_account()
    {
        return $this->belongsTo(Account::class, 'to_account_id', 'id');
    }
    public function getFromAccountCurrencyIdAttribute()
    {
        return $this->from_account->currency()->pluck('id')->toArray()[0];
    }
    public function getFromAccountNameAttribute()
    {
        return $this->from_account->name;
    }
    public function gettoAccountNameAttribute()
    {
        return $this->to_account->name;
    }

    public function log_fund_adjustment()
    {
        $entry =  $this->entry()->create([
            'date' => Carbon::now(),
            'status' => 1,
            'statement' => 'fund_adjustment',
            'ref_currency_id' => $this->from_account_currency_id,
        ]);

        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->from_account_id,
            'debtor' => 0,
            'creditor' => $this->from_amount,
            'ac_debtor' => 0,
            'ac_creditor' => $this->from_amount,
            'exchange_rate' => 1
        ]);
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->to_account_id,
            'debtor' => $this->to_amount,
            'creditor' => 0,
            'ac_debtor' => $this->to_amount / $this->exchange_rate,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);
    }

    // protected $appends = ["party_name", "currency"];

    // public function party()
    // {
    //     return $this->hasOne(Party::class, "id", "beneficiary_id");
    // }

    // public function currency()
    // {
    //     return $this->hasOne(Currency::class, "id", "currency_id");
    // }

    // public function getPartyNameAttribute()
    // {
    //     return $this->party()->first("name")->name;
    // }

    // public function getCurrencyAttribute()
    // {
    //     return $this->currency()->first("name")->name;
    // }

    public function scopeSearch($query, $request)
    {
        $query->when($request->type, function ($query, $type) {
            $query->where("type", $type);
        });
    }
}
