<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends BaseModel implements Document
{
    use HasFactory;
    protected $appends = ['profit'];
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
            'ref_currency_id' => $this->reference_currency_id
        ]);
        // dd($entry);
        // // $entry =  Entry::create([
        // //     'date' => $this->issued_at ?? Carbon::now(),
        // //     'status' => 1,
        // //     'ref_currency_id' => $this->reference_currency_id
        // // ]);
        // // $entry->document()->associate($this)->save();
        // // $this->entry()->associate($entry);
        $this->logAmount()->handleCommision();
    }
    public function dispose()
    {
    }

    public  function logAmount()
    {
        $entry = $this->entry;
        $commission_account_id = Setting::find('commission_account_id')?->value;
        $transfer_expense_account_id = Setting::find('transfer_expense_account_id')?->value;
        $transfer_commission_account_id = Setting::find('transfer_commission_account_id')?->value;
        $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $transactions = [];

        $sender_amount = $this->final_received_amount;
        $reciever_amount = $this->a_received_amount;
        $exchange_difference = 0;
        if ($this->exchange_rate_to_office_currency != $this->exchange_rate_to_delivery_currency) {
            $exchange_difference = $this->a_received_amount - $this->office_amount;
        }
        $transactions[] = [
            'account_id' => $entry->ref_currency->account_id,
            'amount' => $sender_amount,
            'type' => 0,
        ];
        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' => $reciever_amount - $exchange_difference,
            'type' => 1
        ];
        $transactions[] = [
            'account_id' => $transfer_commission_account_id,
            'amount' => $this->transfer_commission,
            'type' => 1, // was 0
        ];
        if ($this->exchange_rate_to_office_currency != $this->exchange_rate_to_delivery_currency) {
            $transactions[] = [
                'account_id' => $exchange_difference_account_id,
                'amount' => $exchange_difference,
                'type' => 1,
            ];
        }
        // if ($this->returned_commission) {
        //     $transactions[] = [
        //         'account_id' => $returned_commission_account_id,
        //         'creditor' => $this->returned_commission,
        //     ];
        // }
        if ($this->other_amounts_on_sender) {
            $transactions[] = [
                'account_id' => $transfer_expense_account_id,
                'amount' => $this->other_amounts_on_sender,
                'type' => 1,
            ];
        }


        // office entry 
        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' => $this->a_received_amount - $exchange_difference,
            'type' => 0,
        ];
        if ($this->returned_commission) {
            $transactions[] = [
                'account_id' => $returned_commission_account_id,
                'amount' => $this->returned_commission,
                'type' => 1,
            ];
        }
        if ($this->office_commission)
            $transactions[] = [
                'account_id' => $transfer_commission_account_id,
                'amount' => $this->office_commission,
                'type' => 0,
            ];
        $amount = $this->office_amount - $this->returned_commission;
        $transactions[] = [
            'account_id' => $entry->ref_currency->account_id,
            'amount' => $amount,
            'type' => 1
            // 'creditor' => $this->a_received_amount - $exchange_difference - $this->office_commission - $this->other_amounts_on_sender - $this->returned_commission,
        ];

        // dd($transactions);
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
                'exchange_rate' => 1,
                'ac_debtor' => !$transaction['type'] ? $transaction['amount'] : 0,
                'ac_creditor' => !$transaction['type'] ? $transaction['amount'] : 0,
            ]);
        }
        return $this;
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
        return $this->belongsTo(Party::class, 'reciever_party_id');
    }
    public function office()
    {
        return $this->belongsTo(Party::class, 'office_id');
    }
    public function getProfitAttribute()
    {
        return $this->final_received_amount - $this->other_amounts_on_receiver - $this->office_amount;
    }
}
