<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exchange extends BaseModel implements Document
{

    protected $appends = ["party_name", "user_name"];
    protected $with = ['details'];
    protected $casts = [
        'amount' => Rounded::class,

    ];
    use HasFactory;

    public function party()
    {
        return $this->hasOne(Party::class, "id", "beneficiary_id")->withDefault(["name" => "زبون عام"]);
    }
    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class);
    // }
    // public function reference_currency()
    // {
    //     return $this->belongsTo(Currency::class);
    // }

    public function getPartyNameAttribute()
    {
        return $this->party()->first("name")?->name ?? "زبون عام";
    }

    // public function getCurrencyNameAttribute()
    // {
    //     return $this->currency->name;
    // }
    public function details()
    {
        return $this->hasMany(ExchangeDetail::class);
    }
    // public function getDetailsAfterAmountAttribute()
    // {
    //     return $this->details()->sum('amount_after');
    // }
    public function confirm()
    {
        $entry = $this->entry()->create([
            'user_id' => request('user_id'),
            'date' => $this->date,
            'status' => 1,
            'document_sub_type' => 2,
            'statement' => "حركة صرافة",
        ]);

        // EntryTransaction::create([
        //     'entry_id' => $entry->id,
        //     'account_id' => $this->user_account_id, //   $this->currency->account_id
        //     'currency_id' => $this->currency_id,
        //     'debtor' => 0,
        //     'creditor' => $this->amount,
        //     'transaction_type' => 0,
        //     'ac_debtor' => 0,
        //     'ac_creditor' => $this->amount_after,
        //     'exchange_rate' => $this->exchange_rate
        // ]);
        $exchange_profit_account_id = Account::find(3)->id;
        $profit = abs($this->profit);
        $minus = $this->profit < 0;
        EntryTransaction::create([
            'entry_id' => $entry->id,
            'account_id' => $exchange_profit_account_id,
            'currency_id' => 1,
            'debtor' => $minus ? $profit : 0,
            'transaction_type' => $minus ? 1 : 0,
            'creditor' => !$minus ? $profit : 0,
            'ac_debtor' => $minus ? $profit : 0,
            'ac_creditor' => !$minus ? $profit : 0,
            'exchange_rate' => 1
        ]);


        $this->details()->each(function ($detail) use ($entry) {
            $detail->log($entry);
        });
    }
    public function dispose()
    {
        // $old_entry = $this->entry;
        $old_entry = $this->entries()->orderBy('id', 'desc')->first();
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
    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }
    public function entries()
    {
        return $this->morphMany(Entry::class, 'document');
    }
    public function getUserNameAttribute()
    {
        return DB::table("users")->where('id', $this->user_id)->first()->name;
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
        $sortBy = $request->sortBy;
        $sortDesc = $request->sortDesc;
        $custom_fields = [];
        if (!$sortBy && !$sortDesc)
            $query->orderby('exchanges.id', 'desc');
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
    public function scopeSearch($query, $request)
    {
        $query->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        });

        $query->when($request->user_action_id, function ($query, $user_action_id) {
            $query->where('user_id', $user_action_id);
        });

        $query->when($request->from_currency_id, function ($query, $from_currency_id) {
            $query->join('exchange_details', 'exchange_details.exchange_id', 'exchanges.id')
                ->where('exchange_details.type', 1)
                ->where('exchange_details.currency_id', $from_currency_id);
        });

        $query->when($request->to_currency_id, function ($query, $to_currency_id) {
            $query->join('exchange_details', 'exchange_details.exchange_id', 'exchanges.id')
                ->where('exchange_details.type', 2)
                ->where('exchange_details.currency_id', $to_currency_id);
        });
        // $query->when($request->transfer_id, function ($query, $id) {
        //     $query->where('id', $id);
        // });
        $query->when($request->party_id, function ($query, $party_id) {
            $query->where('beneficiary_id', $party_id);
        });
        $query->when($request->from && $request->to, function ($query, $from) use ($request) {
            $query->where(DB::raw('date::date'), '>=', $request->from)
                ->where(DB::raw('date::date'), '<=', $request->to);
        });
        $query->when($request->user_id, function ($q, $user_id) {
            if (auth()->user()['role'] != 1) {
                $q->where('user_id',   $user_id);
            }
        });
    }
}
