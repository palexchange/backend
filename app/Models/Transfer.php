<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends BaseModel implements Document
{
    use HasFactory;
    public function confirm()
    {
        $entry =  Entry::create([
            'date' => $this->issued_at,
            'status' => 0,
            'ref_currency_id' => $this->reference_currency_id
        ]);
        $entry->document()->associate($this)->save();
        $this->entry()->save($entry);
        $this->logAmount()->handleCommision();
    }
    public function dispose()
    {
    }

    private function logAmount()
    {
        $entry = $this->entry;
        $commision_account_id=80;
        $transfer_commision_account_id=81;
        $transactions = [];
        if ($entry->type == 1) {
            $sender_amount = $this->received_amount + $this->commision;
            $transactions += [
                'account_id'=>$entry->sender_party->account_id,
                'debtor'=>$sender_amount,
            ];
            $transactions += [
                'account_id'=>$transfer_commision_account_id,
                'creditor'=>$this->commision,
            ];
            $transactions += [
                'account_id'=>$transfer_commision_account_id,
                'creditor'=>$this->office_commision,
            ];
            
        }

        return $this;
    }

    private function handleCommision()
    {
        return $this;
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
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
}
