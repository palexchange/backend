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
            'account_id' => $this->user_account_id, //   $this->currency->account_id
            'currency_id' => $this->currency_id,
            'debtor' => $this->amount,
            'creditor' => 0,
            'transaction_type' => 1,
            'ac_debtor' => $this->amount_after,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);


        // profit transactoin from currency account 
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $this->user_account_id,
            'currency_id' => $this->currency_id,
            'debtor' => $this->profit * $this->exchange_rate,
            'creditor' => 0,
            'transaction_type' => 1,
            'ac_debtor' => $this->profit,
            'ac_creditor' => 0,
            'exchange_rate' => $this->exchange_rate
        ]);

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
    }
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
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
}
