<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Receipt extends BaseModel implements Document
{
    use HasFactory;
    protected $appends = ['from_account_name', 'to_account_name', 'user_name', 'currency_name'];
    public function confirm()
    {
        if ($this->type == 3) {
            $this->log_fund_adjustment();
        }
        if ($this->type == 1 || $this->type == 2) {
            $this->log_receipt();
        }
    }
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }
    public function getUserNameAttribute()
    {
        return DB::table("users")->where('id', $this->user_id)->first()->name;
    }
    public function getFromAccountAttribute()
    {
        return DB::table("accounts")->where('id', $this->from_account_id)->first();
    }
    public function getToAccountAttribute()
    {
        return DB::table("accounts")->where('id', $this->to_account_id)->first();
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
            'user_id' => request('user_id'),
            'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
            'status' => 1,
            'document_sub_type' => 3,
            'statement' => 'fund_adjustment',
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
            'user_id' => request('user_id'),
            'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
            'status' => 1,
            'document_sub_type' => $this->type == 1 ? 4 : (($this->expenses_account_id ? 7 : null) ?? 5),
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
            'transaction_type' => $this->expenses_account_id ? 7 : ($this->type == 1 ? 0 : 1),
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
            'transaction_type' => $this->expenses_account_id ? 7 : ($this->type == 1 ? 1 : 0),
            'exchange_rate' => $this->exchange_rate
        ]);
        if ($this->type == 2 && $this->expenses_account_id) {
            EntryTransaction::create([
                'entry_id' => $entry->id,
                'account_id' => $this->expenses_account_id,
                'currency_id' => $this->currency_id,
                'debtor' =>  $this->from_amount,
                'creditor' =>   0,
                'ac_debtor' => ($this->from_amount / $this->exchange_rate),
                'ac_creditor' => 0,
                'transaction_type' => 7,
                'exchange_rate' => $this->exchange_rate
            ]);
            EntryTransaction::create([
                'entry_id' => $entry->id,
                'account_id' => $this->from_account_id,
                'currency_id' => $this->currency_id,
                'debtor' =>  0,
                'creditor' =>     $this->from_amount,
                'ac_debtor' => 0,
                'ac_creditor' => ($this->from_amount / $this->exchange_rate),
                'transaction_type' =>   7,
                'exchange_rate' => $this->exchange_rate
            ]);
        }
    }



    public function getCurrencyNameAttribute()
    {
        return DB::table("currencies")->where('id', $this->currency_id)->first()->name;
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
        $query->when($request->user_id, function ($query, $user_id) {
            if (auth()->user()['role'] != 1) {
                $query->where("user_id", $user_id);
            }
        });
    }
    public function getUserAccountIdAttribute()
    {
        $user = User::find(auth()->user()->id);
        return $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', $this->currency_id)
            ->first(['accounts.id'])->id;
    }
    public function scopeSort($query, $request)
    {
        $sortBy = $request->sortBy;
        $sortDesc = $request->sortDesc;
        $custom_fields = [];
        if (!$sortBy && !$sortDesc)
            $query->orderby('id', 'desc');
        if ($sortBy && $sortDesc) {
            foreach ($sortBy as $index => $field) {
                $desc = $sortDesc[$index] == 'true' ? "desc" : "asc";
                if (!isset($custom_fields[$field])) {
                    $query->orderBy($field, $desc);
                } else {
                    $custom_fields[$field]($query, $desc);
                }
            }
        }
    }
    public function entries()
    {
        return $this->morphMany(Entry::class, 'document');
    }
    public function dispose()
    {

        $old_entry = $this->entries()->orderBy('id', 'desc')->first();
        // $old_entry = $entry;
        try {
            DB::beginTransaction();
            $entry = $this->entry()->create([
                'user_id' => request('user_id'),
                'date' => $old_entry->date,
                // 'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
                'status' => 1,
                'document_sub_type' => $old_entry->document_sub_type,
                'statement' => $old_entry->statement,
                'ref_currency_id' => $this->reference_currency_id,
                'inverse_entry_id' =>  $old_entry->id,
                'type' => 2,
            ]);
            foreach ($old_entry->transactions as $transaction) {
                EntryTransaction::create([
                    'entry_id' => $entry->id,
                    'debtor' => $transaction->creditor,
                    'creditor' => $transaction->debtor,
                    'account_id' => $transaction->account_id,
                    'exchange_rate' => $transaction->exchange_rate,
                    'currency_id' => $transaction->currency_id,   //,$this->received_currency_id,
                    'ac_debtor' => $transaction->ac_creditor,
                    'ac_creditor' => $transaction->ac_debtor,
                    'transaction_type' => !$transaction->transaction_type,
                ]);
            }
            $old_entry->type = 2;
            $old_entry->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
