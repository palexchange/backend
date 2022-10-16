<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exchange extends BaseModel implements Document
{

    protected $appends = ["party_name", "currency_name"];
    protected $casts = [
        'amount' => Rounded::class,

    ];
    use HasFactory;

    public function party()
    {
        return $this->hasOne(Party::class, "id", "beneficiary_id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function reference_currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getPartyNameAttribute()
    {
        return $this->party()->first("name")->name;
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currency->name;
    }
    public function details()
    {
        return $this->hasMany(ExchangeDetail::class);
    }
    public function confirm()
    {
        $entry = $this->entry()->create([
            'date' => $this->date,
            'status' => 1,
            'statement' => "حركة صرافة",
            'ref_currency_id' => $this->reference_currency_id
        ]);
        // $entry->document()->associate($this)->save();
        // $this->entry()->associate($entry);
        // $this->logAmount()->handleCommision();
        $exchange_profit_account_id = Account::find(3)->id;
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->currency->account_id,
            'debtor' => $this->amount,
            'creditor' => 0,
            'ac_debtor' => $this->amount_after,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);

        // profit transactin from currency account 
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->reference_currency->account_id,
            'debtor' => $this->profit,
            'creditor' => 0,
            'ac_debtor' => $this->profit,
            'ac_creditor' => 0,
            'exchange_rate' => 1
        ]);
        // profit transactin to currency exchange_profit_account  
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $exchange_profit_account_id,
            'debtor' => 0,
            'creditor' => $this->profit,
            'ac_debtor' => 0,
            'ac_creditor' => $this->profit,
            'exchange_rate' => 1
        ]);
        $this->details()->each(function ($detail) use ($entry) {
            $detail->log($entry);
        });
    }
    public function dispose()
    {
    }
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }
    public function getUserAccountIdAttribute()
    {
        $user = User::find(auth()->user()->id);
        return $user->accounts()->where('status', 1)->where('currency_id', $this->currency()->first("id")->id)->first(['accounts.id'])->id;
    }
}
