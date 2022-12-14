<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends BaseModel
{
    use HasFactory;
    protected $with = ['currency'];
    protected $appends = ['inputs_balance',   'balance', 'user_account_id', 'net_balance'];
    protected $hidden = ['entry_transactions', 'user_accounts'];

    public function currency()
    {
        return $this->belongsTo(Currency::class)->withDefault(['name' => 'must assign a currency', 'id' => 999]);
    }
    public function entry_transactions()
    {
        return $this->hasMany(EntryTransaction::class, 'account_id', 'id');
    }
    public function user_accounts()
    {
        return $this->hasMany(UserAccount::class, 'account_id', 'id');
    }
    public function getBalanceAttribute()
    {
        $amount = $this->entry_transactions()->sum(DB::raw('debtor - creditor'));
        // dd($amount);
        // $amount = $this->entry_transactions()
        //     ->join('entries', 'entries.id', 'entry_transactions.entry_id')
        //     ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getNetBalanceAttribute()
    {
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereNotIn('entries.document_sub_type', [4, 5])
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getInputsBalanceAttribute()
    {
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereIn('entries.document_sub_type', [4, 5])
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    // public function getReciptsBalanceAttribute()
    // {
    //     return $this->entry_transactions()
    //         ->join('entries', 'entries.id', 'entry_transactions.entry_id')
    //         ->where('entries.document_type', 3)
    //         ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
    // }
    // public function getNetBalanceAttribute()
    // {
    //     return $this->balance - $this->recipts_balance;
    // }

    // public function getOutputsAttribute()
    // {
    //     return $this->entry_transactions()
    //         ->join('entries', 'entries.id', 'entry_transactions.entry_id')
    //         ->where('entries.document_type', 3)
    //         ->where('entry_transactions.debtor', 0)
    //         ->sum('entry_transactions.creditor');
    // }
    // public function getInputesAttribute()
    // {
    //     return $this->entry_transactions()
    //         ->join('entries', 'entries.id', 'entry_transactions.entry_id')
    //         ->where('entries.document_type', 3)
    //         ->where('entry_transactions.creditor', 0)
    //         ->sum('entry_transactions.debtor');
    // }
    // public function getNetMainInventoryAttribute()
    // {
    //     if ($this->type_id != 4) return 0;
    //     return //$this->currency_id;
    //         $this->entry_transactions()
    //         ->join('entries', 'entries.id', 'entry_transactions.entry_id')
    //         ->where('entries.document_type', 3)
    //         ->where('entry_transactions.currency_id', $this->currency_id);
    //     // ->sum('entry_transactions.creditor');
    // }
    public function getUserAccountIdAttribute()
    {
        if ($this->user_accounts()) {

            return $this->user_accounts()->where('user_id', auth()->user()->id)->first('id')?->id;
        }
        return null;
    }


    public function scopeSearch($query, $request)
    {
        $query->when($request->type_id, function ($query, $type_id) {
            $query->where("type_id", $type_id);
        });
        $query->when($request->currency_id, function ($query, $currency_id) {
            $query->where("currency_id", $currency_id);
        });
        $query->when($request->is_transaction, function ($query, $is_transaction) {
            $query->where("is_transaction", $is_transaction);
        });
    }
}
