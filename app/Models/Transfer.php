<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends BaseModel implements Document
{
    use HasFactory;
    public static $export_options = ['type' => [0 => "حوالة صادرة", 1 => "حوالة واردة"], 'status' => [1 => "معتمدة"], 'commission_side' => [1 => 'المرسل', 2 => 'المستقبل']];
    protected $appends = ['profit'];
    protected $with = ['sender_party'];
    protected $casts = [
        'transfer_commission' => Rounded::class,
        'received_amount' => Rounded::class,
        'discount_amount' => Rounded::class,
        'final_received_amount' => Rounded::class,
        'a_received_amount' => Rounded::class,
        'to_send_amount' => Rounded::class,
        'office_commission' => Rounded::class,
        'returned_commission' => Rounded::class,
        'office_amount' => Rounded::class,
    ];
    public function confirm()
    {
        $this->entry()->create([
            'date' => $this->issued_at ?? Carbon::now(),
            'status' => 1,
            'statement' => "حركة حوالة",
            'ref_currency_id' => $this->reference_currency_id
        ]);
        $this->logAmount()->handleCommision();
    }
    public function dispose()
    {
    }

    public  function logAmount()
    {

        $user_account_id = $this->user_account_id;
        logger($user_account_id);
        $entry = $this->entry;
        $transfer_profit_account_id = Account::find(2)->id;
        // $commission_account_id = Setting::find('commission_account_id')?->value;
        // $transfer_expense_account_id = Setting::find('transfer_expense_account_id')?->value;
        // $transfer_commission_account_id = Setting::find('transfer_commission_account_id')?->value;
        // $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        // $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $transactions = [];

        $sender_amount = $this->final_received_amount;
        $reciever_amount = $this->a_received_amount;
        $exchange_difference = 0;
        $account_id = $this->delivering_type == 3 ? $this->sender_party->account_id : $this->user_account_id; // $entry->ref_currency->account_id;
        if ($this->exchange_rate_to_office_currency != $this->exchange_rate_to_delivery_currency) {
            $exchange_difference = $this->a_received_amount - $this->office_amount;
        }

        $transactions[] = [
            'account_id' => $account_id, // $this->user_account_id
            'amount' => $sender_amount,
            'transaction_type' => 1,
            'exchange_rate' => 1,
            'type' => 0,
        ];
        $transactions[] = [
            'account_id' => $transfer_profit_account_id,
            'amount' => abs($this->profit),
            'transaction_type' => 1,
            'exchange_rate' => 1,
            'type' => $this->profit > 0 ?  1 : 0,
        ];
        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' => $this->office_amount,
            'transaction_type' => 1,
            'exchange_rate' => 1,
            'type' => 1,
        ];



        foreach ($transactions as $transaction) {
            if (!$transaction['account_id']) {
                // dd($transfer_commission_account_id);
                // dd($transaction);
            }
            EntryTransaction::create([
                'entry_id' => $entry->id,
                'debtor' => !$transaction['type'] ? $transaction['amount'] : 0,
                'creditor' => $transaction['type'] ? $transaction['amount'] : 0,
                'account_id' => $transaction['account_id'],
                'exchange_rate' =>  $transaction['exchange_rate'],
                'currency_id' =>  $transaction['currency_id'] ?? 1, //,$this->received_currency_id,
                'ac_debtor' => !$transaction['type'] ? $transaction['amount'] : 0,
                'ac_creditor' => $transaction['type'] ? $transaction['amount'] : 0,
                'transaction_type' => $transaction['transaction_type'],
            ]);
        }
        if ($this->delivering_type == 1) {
            $trans =  $this->addPartiesTransactions();
            foreach ($trans as $transaction) {
                if (!$transaction['account_id']) {
                    // dd($transfer_commission_account_id);
                    // dd($transaction);
                }
                EntryTransaction::create([
                    'entry_id' => $entry->id,
                    'debtor' =>  $transaction['debtor'],
                    'creditor' => $transaction['creditor'],
                    'account_id' => $transaction['account_id'],
                    'exchange_rate' =>  $transaction['exchange_rate'],
                    'currency_id' =>  $transaction['currency_id'] ?? 1, //,$this->received_currency_id,
                    'ac_debtor' =>  $transaction['ac_debtor'],
                    'ac_creditor' =>  $transaction['ac_creditor'],
                    'transaction_type' => $transaction['transaction_type'],
                ]);
            };
        }
        return $this;
    }
    public function addPartiesTransactions()
    {
        $trans = [];
        /////  sender transactions
        $trans[] = [
            'account_id' => $this->sender_party->account_id,
            'exchange_rate' => $this->exchange_rate_to_delivery_currency,
            'currency_id' => $this->delivery_currency_id,
            'debtor' =>  0,
            'ac_debtor' => 0,
            'creditor' =>  $this->final_received_amount / $this->exchange_rate_to_delivery_currency,
            'ac_creditor' => $this->final_received_amount,
            'transaction_type' => 0,
        ];
        $trans[] = [
            'account_id' => $this->sender_party->account_id,
            'exchange_rate' => $this->exchange_rate_to_delivery_currency,
            'currency_id' => $this->delivery_currency_id,
            'debtor' => $this->final_received_amount / $this->exchange_rate_to_delivery_currency,
            'ac_debtor' =>  $this->final_received_amount,
            'creditor' => 0,
            'ac_creditor' => 0,
            'transaction_type' => 1,
        ];
        /////   receiver  transactions
        $trans[] = [
            'account_id' => $this->reciever_party->account_id,
            'exchange_rate' => $this->exchange_rate_to_reference_currency,
            'currency_id' => $this->received_currency_id,
            'debtor' =>  0,
            'ac_debtor' => 0,
            'creditor' =>   $this->a_received_amount * $this->exchange_rate_to_reference_currency,
            'ac_creditor' => $this->a_received_amount,
            'transaction_type' => 0,
        ];
        $trans[] = [
            'account_id' => $this->reciever_party->account_id,
            'exchange_rate' => $this->exchange_rate_to_reference_currency,
            'currency_id' => $this->received_currency_id,
            'debtor' =>   $this->a_received_amount * $this->exchange_rate_to_reference_currency,
            'ac_debtor' => $this->a_received_amount,
            'creditor' => 0,
            'ac_creditor' => 0,
            'transaction_type' => 1,
        ];

        return $trans;
    }
    private function handleCommision()
    {
        return $this;
    }

    public function entry()
    {
        return $this->morphOne(Entry::class, 'document');
    }

    public function received_currency()
    {
        return $this->belongsTo(Currency::class, 'received_currency_id');
    }
    public function delivery_currency()
    {
        return $this->belongsTo(Currency::class, 'delivery_currency_id');
    }
    public function reference_currency()
    {
        return $this->belongsTo(Currency::class, 'reference_currency_id');
    }
    public function office_currency()
    {
        return $this->belongsTo(Currency::class, 'office_currency_id');
    }
    public function sender_party()
    {
        return $this->belongsTo(Party::class, 'sender_party_id');
    }
    public function reciever_party()
    {
        return $this->belongsTo(Party::class, 'receiver_party_id');
    }
    public function office()
    {
        return $this->belongsTo(Party::class, 'office_id');
    }
    public function getProfitAttribute()
    {
        return $this->final_received_amount - $this->other_amounts_on_receiver - $this->office_amount;
    }
    public function getUserAccountIdAttribute()
    {
        $user = auth()->user();
        $id =  $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', 1)->first(['accounts.id'])->id;
        return $id;
    }
    public static function exportData()
    {
        return  Transfer::join('parties as sender', 'sender.id', 'transfers.sender_party_id')
            ->leftJoin('parties as office', 'office.id', 'transfers.office_id')
            ->leftJoin('currencies as d_c', 'd_c.id', 'transfers.delivery_currency_id')
            ->leftJoin('currencies as r_c', 'r_c.id', 'transfers.received_currency_id')
            ->leftJoin('currencies as of_c', 'of_c.id', 'transfers.office_currency_id')
            ->select(
                '*',
                'transfers.id as id',
                'transfers.status',
                'office.name as office_name',
                'sender.name as sender_name',
                'd_c.name as delivery_currency',
                'r_c.name as received_currency',
                'of_c.name as office_currency'
            )->orderBy('transfers.id', 'asc')->get()->toArray();
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

    // public static function hasAbilityToCreateModelInCurrency($currency_id)
    // {
    //     $accounts_count = auth()->user()->accounts()->where('status', 1)->where('currency_id', $currency_id)->count();
    //     return $accounts_count > 0;
    // }
}
