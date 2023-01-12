<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Entry extends BaseModel
{
    use HasFactory;
    public function document()
    {
        return $this->morphTo();
    }
    public function ref_currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function transactions()
    {
        return $this->hasMany(EntryTransaction::class);
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
    public function scopeSearch($query, $request)
    {
        $query->when($request->statement, function ($q, $statemant) {
            $q->where('statement', '=', $statemant);
        });
        $query->when($request->like_statement, function ($q, $statemant) {
            $q->where('statement', 'LIKE', "%{$statemant}%");
        });
        $query->when($request->id, function ($q, $id) {
            $q->where('id', $id);
        });
        $query->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });
    }
    public function dispose()
    {

        try {
            DB::beginTransaction();
            $entry = $this->create([
                'user_id' => request('user_id'),
                'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
                'status' => 1,
                'document_sub_type' => 1,
                'statement' => $this->statement,
                'ref_currency_id' => $this->reference_currency_id,
                'inverse_entry_id' =>  $this->id
            ]);
            foreach ($this->transactions as $transaction) {
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
            if ($this->document) {
                $this->document->status = 255;
                $this->document->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
