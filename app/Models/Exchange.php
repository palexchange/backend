<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exchange extends BaseModel implements Document
{

    protected $appends = ["currency_name"];
    protected $with = ["user"];
    protected $casts = [
        'amount' => Rounded::class,

    ];
    use HasFactory;

    // public function party()
    // {
    //     return $this->hasOne(Party::class, "id", "beneficiary_id");
    // }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function reference_currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // public function getPartyNameAttribute()
    // {
    //     return $this->party()->first("name")->name;
    // }

    public function getCurrencyNameAttribute()
    {
        return $this->currency->name;
    }
    public function details()
    {
        return $this->hasMany(ExchangeDetail::class);
    }
    public function getDetailsAfterAmountAttribute()
    {
        return $this->details()->sum('amount_after');
    }
    public function confirm()
    {
        $entry = $this->entry()->create([
            'user_id' => request('user_id'),
            'date' => $this->date,
            'status' => 1,
            'document_sub_type' => 2,
            'statement' => "حركة صرافة",
            'ref_currency_id' => $this->reference_currency_id
        ]);

        $exchange_profit_account_id = Account::find(3)->id;
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->user_account_id, //   $this->currency->account_id
            'currency_id' => $this->currency_id,
            'debtor' => $this->amount,
            'creditor' => 0,
            'transaction_type' => 1,
            'ac_debtor' => $this->amount_after,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);

        // $profit_and_lose_account_id = Setting::find('losses_and_profits')?->value;
        // profit transactoin from currency account 
        // EntryTransaction::create([
        //     'entry_id' => $entry->id,
        //     'account_id' => $profit_and_lose_account_id ?? 3,
        //     'currency_id' => 1,
        //     'debtor' => $this->profit - (($this->amount - $this->details_after_amount) * $this->exchange_rate),
        //     'creditor' => 0,
        //     'transaction_type' => 1,
        //     'ac_debtor' => $this->profit - (($this->amount - $this->details_after_amount) * $this->exchange_rate),
        //     'ac_creditor' => 0,
        //     'exchange_rate' => 1
        // ]);

        // profit transactin to currency exchange_profit_account  
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $exchange_profit_account_id,
            'currency_id' => 1,
            'debtor' => 0,
            'transaction_type' => 0,
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
        $old_entry = $this->entry;
        try {
            DB::beginTransaction();
            $entry = $this->entry()->create([
                'user_id' => request('user_id'),
                'date' => Carbon::now()->toDateString(),
                'status' => 1,
                'document_sub_type' => 2,
                'statement' => $old_entry->statement,
                'ref_currency_id' => $this->reference_currency_id,
                'inverse_entry_id' =>  $old_entry->id
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
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
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
    public  static function exportData()
    {
        Exchange::all();
    }
    public static  $exportHeaders =
    [
        "id",
        "issued_at",
        "type",
        "status",
        "commission_side",
        "received_amount",
        "to_send_amount",
        "exchange_rate_to_reference_currency",
        "exchange_rate_to_delivery_currency",
        "exchange_rate_to_office_currency",
        "office_amount_in_office_currency",
        "office_name",
        "sender_name",
        "delivery_currency",
        "received_currency",
        "office_currency",
        "profit",
    ];
    public static   function exportheaders()
    {
        return [
            __("id"),
            __("issued_at"),
            __("type"),
            __("status"),
            __("commission_side"),
            __("received_amount"),
            __("to_send_amount"),
            __("exchange_rate_to_reference_currency"),
            __("exchange_rate_to_delivery_currency"),
            __("exchange_rate_to_office_currency"),
            __("office_amount_in_office_currency"),
            __("office_name"),
            __("sender_name"),
            __("delivery_currency"),
            __("received_currency"),
            __("office_currency"),
            __("profit")
        ];
    }
    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
        $query->when($request->user_id, function ($q, $user_id) {
            if (auth()->user()['role'] != 1) {
                $q->where('user_id',   $user_id);
            }
        });
    }
}
