<?php

namespace App\Models;

use App\Casts\Rounded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends BaseModel implements Document
{
    // protected $with = ["ben"];
    protected $appends = ["party_name", "currency_name"];
    // protected $hidden =["ben"];
    protected $casts = [
        'amount' => Rounded::class,
        'amount_after' => Rounded::class,
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
        $entry =  Entry::create([
            'date' => $this->date,
            'status' => 1,
            'ref_currency_id' => $this->reference_currency_id
        ]);
        $entry->document()->associate($this)->save();
        $this->entry()->associate($entry);
        // $this->logAmount()->handleCommision();
        EntryTransaction::create([
            'entry_id'=>$entry->id,
            'account_id'=>$this->currency->account_id,
            'debtor'=>$this->amount,
            'creditor'=>0,
            'ac_debtor'=>$this->amount_after,
            'ac_creditor'=>0,
            'exchange_rate'=>$this->exchange_rate
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
        return $this->belongsTo(Entry::class);
    }
}
