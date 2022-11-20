<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends BaseModel
{
    use HasFactory;
    protected $with = ['currency'];
    protected $appends = ['balance', 'user_account_id'];
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
        return $this->entry_transactions()->sum(DB::raw('debtor - creditor'));
    }
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
