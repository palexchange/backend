<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends BaseModel
{
    use HasFactory;
    protected $with = ['currency', 'children'];
    protected $appends = ['inputs_balance',   'balance', 'user_account_id', 'net_balance', 'start_net_balance', 'inventory_balance', 'usd_balance'];
    protected $hidden = ['entry_transactions', 'user_accounts'];

    public function party()
    {
        return $this->hasOne(Party::class)->withDefault(['name' => 'must assign a currency', 'id' => 999]);
    }
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
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id', 'id');
    }
    public function getBalanceAttribute()
    {
        $amount = $this->entry_transactions()
            ->sum(DB::raw('debtor - creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getUsdBalanceAttribute()
    {
        $amount = $this->entry_transactions()
            ->sum(DB::raw('ac_debtor - ac_creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }

    public function getNetBalanceAttribute()
    {
        if ($this->balance == 0) return  0;
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereNotIn('entries.document_sub_type',  [4, 5]) // 
            ->whereNotIn('entry_transactions.transaction_type',  [5, 6, 8, 10, 2, 3, 4, 9])
            ->where('entries.type',  1)
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getStartNetBalanceAttribute()
    {
        // if ($this->balance == 0) return  0;
        // if ($this->id != 11) return  0;
        DB::connection()->enableQueryLog();
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereNotIn('entries.document_sub_type',  [4, 5, 6]) // 
            ->where('entries.type',  1)
            ->whereNotIn('entry_transactions.transaction_type',  [5, 6, 8, 10, 2, 3, 4, 9])
            ->whereDate('entry_transactions.created_at', '<', Carbon::today()->toDateString())
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        $queries = DB::getQueryLog();
        // dd($queries);
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getInventoryBalanceAttribute()
    {
        $amount = $this->entry_transactions()->whereNotIn('transaction_type', [ 9, 3, 4, 11, 16, 15]) // commissions
            ->sum(DB::raw('debtor - creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }

    public function getNetBalanceTodayAttribute()
    {
        // if ($this->balance == 0) return  0;
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereDate(DB::raw('entries.date::date'), '=', Carbon::today()->toDateString())
            // ->whereIn('entries.transaction_type',  [2])
            ->whereIn('transaction_type',  [17, 18, 16, 15])
            ->where('entries.type',  1)
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }
    public function getMidAttribute()
    {

        $mid = Stock::where('currency_id', $this->currency_id)->first()?->mid ?? 0;
        return $mid;
        if ($this->currency_id != 4) {
            return $this->net_balance / $mid;
        } else {
            return $this->net_balance * $mid;
        }
    }
    public function getCloseMidAttribute()
    {
        $mid = Stock::where('currency_id', $this->currency_id)->first()?->close_mid ?? 0;
        return $mid;
    }
    public function getInputsBalanceAttribute()
    {

        // if ($this->balance == 0) return  0;
        $amount = $this->entry_transactions()
            ->join('entries', 'entries.id', 'entry_transactions.entry_id')
            ->whereIn('entries.document_sub_type', [4, 5])
            ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
        if (gettype($amount) == 'string') {
            $amount =  substr($amount, 0, 8);
        }
        return  $amount;
    }

    public function getUserAccountIdAttribute()
    {
        if ($this->user_accounts()) {
            return $this->user_accounts()->where('user_id', auth()->user()->id)->first('id')?->id;
        }
        return null;
    }

    // public function getOpenNetBalanceInDollarAttribute()
    // {
    //     $mid = Stock::where('currency_id', $this->currency_id)->first()?->mid ?? 0;
    //     if ($mid == 0) return 0;
    //     if ($this->currency_id != 4) {
    //         return $this->open_net_balance / $mid;
    //     } else {
    //         return $this->open_net_balance * $mid;
    //     }
    // }

    // public function getOpenNetBalanceAttribute()
    // {
    // if ($this->balance == 0) return  0;
    //     $amount = $this->entry_transactions()
    //         ->join('entries', 'entries.id', 'entry_transactions.entry_id')
    //         ->whereNotIn('entries.document_sub_type',  [4, 5])
    //         ->where('entries.date', '<', Carbon::today())
    //         ->sum(DB::raw('entry_transactions.debtor - entry_transactions.creditor'));
    //     if (gettype($amount) == 'string') {
    //         $amount =  substr($amount, 0, 8);
    //     }
    //     return  $amount;
    // }


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
        $query->when($request->no_party, function ($query, $is_transaction) {
            $query->where("type_id", '!=', 1);
        });
    }
}
