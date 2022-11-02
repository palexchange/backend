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
        if ($this->type == 1 || $this->type == 2) {
            $this->log_receipt();
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
        $currency = $this->from_account->currency()->pluck('id')->toArray();
        return isset($currency[0]) ? $currency[0] : 1;
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
            'document_sub_type' => 3,
            'statement' => 'fund_adjustment',
            'ref_currency_id' => $this->from_account_currency_id,
        ]);

        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->from_account_id,
            'currency_id' => $this->from_account->currency_id,
            'debtor' => 0,
            'creditor' => $this->from_amount,
            'ac_debtor' => 0,
            'ac_creditor' => $this->from_amount,
            'exchange_rate' => 1
        ]);
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->to_account_id,
            'currency_id' => $this->to_account->currency_id,
            'debtor' => $this->to_amount,
            'creditor' => 0,
            'ac_debtor' => $this->to_amount / $this->exchange_rate,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);
    }
    public function log_receipt()
    {
        $entry =  $this->entry()->create([
            'date' => Carbon::now(),
            'status' => 1,
            'document_sub_type' => $this->type == 1 ? 4 : 5,
            'statement' => $this->statement,
            'ref_currency_id' => $this->currency_id,
        ]);
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->from_account_id,
            'currency_id' => $this->currency_id,
            'debtor' => $this->type == 1 ? 0 : $this->from_amount,
            'creditor' => $this->type == 1 ?  $this->from_amount : 0,
            'ac_debtor' => $this->type == 1 ? 0 : ($this->from_amount / $this->exchange_rate),
            'ac_creditor' => $this->type == 1 ? ($this->from_amount / $this->exchange_rate) : 0,
            'transaction_type' => $this->type == 1 ? 0 : 1,
            'exchange_rate' => $this->exchange_rate
        ]);
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->to_account_id,
            'currency_id' => $this->currency_id,
            'debtor' => $this->type == 1 ? $this->from_amount : 0,
            'creditor' => $this->type == 1 ?    0 : $this->from_amount,
            'ac_debtor' => $this->type == 1 ? ($this->from_amount / $this->exchange_rate) : 0,
            'ac_creditor' => $this->type == 1 ? 0 : ($this->from_amount / $this->exchange_rate),
            'transaction_type' => $this->type == 1 ? 1 : 0,
            'exchange_rate' => $this->exchange_rate
        ]);
    }

    // protected $appends = ["party_name", "currency"];

    // public function party()
    // {
    //     return $this->hasOne(Party::class, "id", "beneficiary_id");
    // }

    public function currency()
    {
        return $this->hasOne(Currency::class);
    }

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
    public function getUserAccountIdAttribute()
    {
        $user = User::find(auth()->user()->id);
        return $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', $this->currency()->first("id")->id)
            ->first(['accounts.id'])->id;
    }
}
