<?php

namespace App\Models;

use App\Casts\Rounded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\This;

class Transfer extends BaseModel implements Document
{
    use HasFactory;
    public static $export_options = ['type' => [0 => "حوالة صادرة", 1 => "حوالة واردة"], 'status' => [0 => "مسودة", 1 => "معتمدة"], 'commission_side' => [1 => 'المرسل', 2 => 'المستقبل']];
    protected $appends = ['profit'];
    protected $with = ['receiver_party', 'sender_party', 'image', 'office', 'user'];
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
        try {
            DB::beginTransaction();
            $entry = $this->entry()->create([
                'user_id' => request('user_id'),
                'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
                'status' => 1,
                'document_sub_type' => 1,
                'statement' => $this->getTypeStatement(),
                'ref_currency_id' => $this->reference_currency_id
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        if ($this->type == 1) {
            $this->logIncomingAmount($entry)->handleCommision();
            $trans =  $this->addIncomingPartiesTransactions();
            foreach ($trans as $transaction) {
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
        } else {
            $this->logAmount($entry)->handleCommision();
            $trans =  $this->addOutcomingPartiesTransactions();
            foreach ($trans as $transaction) {
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
    }
    // public function dispose()
    // {

    //     $old_entry = $this->entry;
    //     try {
    //         DB::beginTransaction();
    //         $entry = $this->entry()->create([
    //             'user_id' => request('user_id'),
    //             'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
    //             'status' => 1,
    //             'document_sub_type' => 1,
    //             'statement' => $old_entry->statement,
    //             'ref_currency_id' => $this->reference_currency_id,
    //             'inverse_entry_id' =>  $old_entry->id
    //         ]);
    //         foreach ($old_entry->transactions as $transaction) {
    //             EntryTransaction::create([
    //                 'entry_id' => $entry->id,
    //                 'debtor' => $transaction->creditor,
    //                 'creditor' => $transaction->debtor,
    //                 'account_id' => $transaction->account_id,
    //                 'exchange_rate' => $transaction->exchange_rate,
    //                 'currency_id' => $transaction->currency_id,   //,$this->received_currency_id,
    //                 'ac_debtor' => $transaction->ac_creditor,
    //                 'ac_creditor' => $transaction->ac_debtor,
    //                 'transaction_type' => !$transaction->transaction_type,
    //             ]);
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         throw $e;
    //     }
    // }

    public  function logAmount($entry)
    {

        $user_account_id = $this->user_account_id;
        $transfer_profit_account_id = Account::find(2)->id;
        // $commission_account_id = Setting::find('commission_account_id')?->value;
        // $transfer_commission_account_id = Setting::find('transfer_commission_account_id')?->value;
        // $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $transactions = [];

        $sender_amount =    $this->delivering_type == 2 ? $this->final_received_amount : $this->to_send_amount; // $this->final_received_amount;
        $reciever_amount = $this->a_received_amount;
        $exchange_difference = 0;
        $account_id = $this->delivering_type == 3 ? $this->sender_party->account_id : $this->user_account_id; // $entry->ref_currency->account_id;
        $usd_commission_account_id = $this->delivering_type == 3 ? $this->sender_party->account_id : $this->get_user_account_id(1); // $entry->ref_currency->account_id;
        if ($this->exchange_rate_to_office_currency != $this->exchange_rate_to_delivery_currency) {
            $exchange_difference = $this->a_received_amount - $this->office_amount;
        }

        $transactions[] = [
            'account_id' => $account_id, // $this->user_account_id
            'amount' => $sender_amount,
            'ac_amount' => $sender_amount * $this->exchange_rate_to_delivery_currency,
            'transaction_type' => 1,
            'exchange_rate' => $this->exchange_rate_to_delivery_currency,
            'currency_id' => $this->delivery_currency_id,
            'type' => 0,
        ];


        if ($this->transfer_commission > 0) {
            $transactions[] = [
                'account_id' => $usd_commission_account_id, // $this->user_account_id
                'amount' => $this->transfer_commission,
                'transaction_type' => 2, //
                'exchange_rate' => 1,
                'currency_id' => 1,
                'type' => 0,
            ];
        }
        // if ($this->delivering_type == 2) {
        //     $transactions[] = [
        //         'account_id' => $account_id,
        //         'amount' => abs($this->profit),
        //         'transaction_type' => 1,
        //         'exchange_rate' => 1,
        //         'type' => 0,
        //     ];
        // }

        // $transactions[] = [
        //     'account_id' => $exchange_difference_account_id,
        //     'amount' => $this->ExchangeDiffernce(),
        //     'transaction_type' => 1,
        //     'exchange_rate' => 1,
        //     'type' => 0,
        // ];

        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' => $this->office_amount_in_office_currency ?? $this->office_amount,
            'ac_amount' => $this->office_amount,
            'transaction_type' => 1,
            'currency_id' => $this->office_currency_id,
            'exchange_rate' => 1 / Stock::find($this->office_currency_id)->start_selling_price,
            'type' => 1,
        ];
        $transactions[] = [
            'account_id' => $transfer_profit_account_id,
            'amount' => abs($this->profit),
            'transaction_type' => 1,
            'exchange_rate' => 1,
            'type' => $this->profit > 0 ?  1 : 0,
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
                'ac_debtor' => !$transaction['type'] ? ($transaction['ac_amount'] ?? $transaction['amount']) : 0,
                'ac_creditor' => $transaction['type'] ? ($transaction['ac_amount'] ?? $transaction['amount']) : 0,
                'transaction_type' => $transaction['transaction_type'],
            ]);
        }

        return $this;
    }
    public  function logIncomingAmount($entry)
    {

        $transfer_profit_account_id = Account::find(2)->id;
        $transactions = [];
        $user_account_id = $this->user_account_id;
        $sender_amount = $this->final_received_amount;
        $reciever_amount = $this->a_received_amount;
        $exchange_difference = 0;
        $account_id = $this->receiver_party_id; // $entry->ref_currency->account_id;
        if ($this->exchange_rate_to_office_currency != $this->exchange_rate_to_delivery_currency) {
            $exchange_difference = $this->a_received_amount - $this->office_amount;
        }
        // $amount = $this->delivering_type == 2  ? ($this->office_amount_in_office_currency ?? $this->office_amount) : $this->office_amount;
        $amount =  $this->office_amount_in_office_currency == 0 ?  $this->office_amount : $this->office_amount_in_office_currency;
        $transactions[] = [
            'account_id' => $this->office->account_id, // $this->user_account_id moew moew moew moew moew
            'amount' => $amount,
            'ac_amount' => $amount * $this->exchange_rate_to_office_currency,
            'transaction_type' => 1,
            'currency_id' =>  $this->office_currency_id,
            'exchange_rate' => $this->exchange_rate_to_office_currency,
            'type' => 0,
        ];

        $transactions[] = [
            'account_id' =>  $user_account_id,
            'amount' => $this->received_amount,
            'ac_amount' => $this->a_received_amount,
            'currency_id' => $this->received_currency_id,
            'transaction_type' => 1,
            'exchange_rate' => $this->delivering_type == 2 ? 1 : number_format($this->received_amount / $this->a_received_amount, 3, '.', ""),
            'type' => 1,
        ];

        $transactions[] = [
            'account_id' => $transfer_profit_account_id,
            'amount' => abs($this->profit),
            'transaction_type' => 1,
            'exchange_rate' => 1,
            'type' =>  $this->profit > 0 ?  1 : 0,
        ];
        // if ($this->delivering_type == 2 && $this->office_commission > 0) {

        //     $transactions[] = [
        //         'account_id' => $this->get_user_account_id(1),
        //         'amount' => abs($this->profit),
        //         'transaction_type' => 1,
        //         'exchange_rate' => 1,
        //         'type' => 0,
        //     ];
        // }
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
                'ac_debtor' => !$transaction['type'] ?  (isset($transaction['ac_amount']) ? $transaction['ac_amount'] : $transaction['amount']) : 0,
                'ac_creditor' => $transaction['type'] ? (isset($transaction['ac_amount']) ? $transaction['ac_amount'] : $transaction['amount']) : 0,
                'transaction_type' => $transaction['transaction_type'],
            ]);
        }
        return $this;
    }
    public function addIncomingPartiesTransactions()
    {
        $trans = [];

        /////   receiver  transactions
        if ($this->delivering_type == 2) {
            $trans[] = [
                'account_id' => $this->receiver_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>   $this->received_amount,
                'ac_creditor' => $this->received_amount / $this->exchange_rate_to_reference_currency,
                'transaction_type' => 0,
            ];
            $trans[] = [
                'account_id' => $this->receiver_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>   $this->received_amount,
                'ac_debtor' => $this->received_amount / $this->exchange_rate_to_reference_currency,
                'creditor' => 0,
                'ac_creditor' => 0,
                'transaction_type' => 1,
            ];
        } else {

            $trans[] = [
                'account_id' => $this->receiver_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>   $this->to_send_amount * $this->exchange_rate_to_reference_currency,
                'ac_creditor' => $this->to_send_amount,
                'transaction_type' => 0,
            ];
            if ($this->delivering_type != 4) {

                $trans[] = [
                    'account_id' => $this->receiver_party->account_id,
                    'exchange_rate' => $this->exchange_rate_to_reference_currency,
                    'currency_id' => $this->received_currency_id,
                    'debtor' =>   $this->to_send_amount * $this->exchange_rate_to_reference_currency,
                    'ac_debtor' => $this->to_send_amount,
                    'creditor' => 0,
                    'ac_creditor' => 0,
                    'transaction_type' => 1,
                ];
            }
        }

        return $trans;
    }
    public function addOutcomingPartiesTransactions()
    {
        $trans = [];
        /////  sender transactions
        if ($this->delivering_type == 1) {
            if ($this->receiver_party->account_id != $this->sender_party->account_id) {
                $trans[] = [
                    'account_id' => $this->sender_party->account_id,
                    'exchange_rate' => $this->exchange_rate_to_delivery_currency,
                    'currency_id' => $this->delivery_currency_id,
                    'debtor' =>  0,
                    'ac_debtor' => 0,
                    'creditor' => $this->to_send_amount,
                    'ac_creditor' =>  $this->to_send_amount * $this->exchange_rate_to_delivery_currency,
                    'transaction_type' => 0,
                ];
                $trans[] = [
                    'account_id' => $this->sender_party->account_id,
                    'exchange_rate' => $this->exchange_rate_to_delivery_currency,
                    'currency_id' => $this->delivery_currency_id,
                    'debtor' => $this->to_send_amount,
                    'ac_debtor' =>  $this->to_send_amount * $this->exchange_rate_to_delivery_currency,
                    'creditor' => 0,
                    'ac_creditor' => 0,
                    'transaction_type' => 1,
                ];
            };
        }

        /////   receiver  transactions
        if ($this->delivering_type == 2) {
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>   $this->final_received_amount * $this->exchange_rate_to_reference_currency,
                'ac_creditor' => $this->final_received_amount,
                'transaction_type' => 0,
            ];
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>   $this->final_received_amount * $this->exchange_rate_to_reference_currency,
                'ac_debtor' => $this->final_received_amount,
                'creditor' => 0,
                'ac_creditor' => 0,
                'transaction_type' => 1,
            ];
        } else {
            $trans[] = [
                'account_id' => $this->receiver_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>   $this->received_amount,
                'ac_creditor' => $this->a_received_amount,
                'transaction_type' => 0,
            ];
            $trans[] = [
                'account_id' => $this->receiver_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>   $this->received_amount,
                'ac_debtor' => $this->a_received_amount,
                'creditor' => 0,
                'ac_creditor' => 0,
                'transaction_type' => 1,
            ];
        }

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
    public function entries()
    {
        return $this->morphMany(Entry::class, 'document');
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
    public function receiver_party()
    {
        return $this->belongsTo(Party::class, 'receiver_party_id');
    }
    public function office()
    {
        return $this->belongsTo(Party::class, 'office_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getProfitAttribute()
    {
        if ($this->type == 0) {
            if ($this->delivering_type == 2) {
                return  number_format($this->transfer_commission, 3, '.', "");
            }
            return $this->final_received_amount - $this->other_amounts_on_receiver - $this->office_amount;
        } else {
            if ($this->delivering_type == 2) {
                return
                    number_format($this->office_amount - $this->a_received_amount, 3, '.', "");
            }
            return $this->office_amount - $this->a_received_amount   - $this->returned_commision + $this->office_commission;
        }
    }
    public function ExchangeDiffernce()
    {
        // $transfer_commission = $this->office_commission_type == 0 ?
        //     $this->transfer_commission
        //     : (($this->transfer_commission / 100) * $this->to_send_amount);

        // $returned_commission = $this->returned_commission_type == 0 ?
        //     $this->returned_commission
        //     : (($this->returned_commission / 100) * $this->officeAmount);

        // $returned_commission = $this->office_commission_type == 0 ?
        //     $this->returned_commission
        //     : (($this->returned_commission / 100) * $this->officeAmount);

        // return $this->profit - ($this->final_received_amount - $this->office_amount);
        return $this->profit + $this->office_commission - $this->transfer_commission - $this->returned_commission;
    }
    public function getUserAccountIdAttribute()
    {
        $user = auth()->user();
        $id =  $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', $this->type == 0 ?  $this->delivery_currency_id : $this->received_currency_id)->first(['accounts.id'])->id;
        return $id;
    }
    public function getDeliveryCurrencyNameAttribute()
    {
        return $this->delivery_currency->name;
    }
    public function getReceivedCurrencyNameAttribute()
    {
        return $this->received_currency->name;
    }
    public function getSenderNameAttribute()
    {
        return $this->sender_party->name;
    }
    public function getReceiverNameAttribute()
    {
        return $this->receiver_party->name;
    }
    public function getTransferStatusAttribute()
    {
        return [0 => 'مسودة', 1 => 'معتمدة', 255 => 'ملغاة'][$this->status];
    }
    // public static function exportData()
    // {
    //     return  Transfer::join('parties as sender', 'sender.id', 'transfers.sender_party_id')
    //         ->leftJoin('parties as office', 'office.id', 'transfers.office_id')
    //         ->leftJoin('currencies as d_c', 'd_c.id', 'transfers.delivery_currency_id')
    //         ->leftJoin('currencies as r_c', 'r_c.id', 'transfers.received_currency_id')
    //         ->leftJoin('currencies as of_c', 'of_c.id', 'transfers.office_currency_id')
    //         ->select(
    //             '*',
    //             'transfers.id as id',
    //             'transfers.status',
    //             'office.name as office_name',
    //             'sender.name as sender_name',
    //             'd_c.name as delivery_currency',
    //             'r_c.name as received_currency',
    //             'of_c.name as office_currency'
    //         )->orderBy('transfers.id', 'asc')->get()->toArray();
    // }
    // public static  $exportHeaders =
    // [
    //     "id",
    //     "issued_at",
    //     "type",
    //     "status",
    //     "commission_side",
    //     "received_amount",
    //     "to_send_amount",
    //     "exchange_rate_to_reference_currency",
    //     "exchange_rate_to_delivery_currency",
    //     "exchange_rate_to_office_currency",
    //     "office_amount_in_office_currency",
    //     "office_name",
    //     "sender_name",
    //     "delivery_currency",
    //     "received_currency",
    //     "office_currency",
    //     "profit",
    // ];


    // public static function hasAbilityToCreateModelInCurrency($currency_id)
    // {
    //     $accounts_count = auth()->user()->accounts()->where('status', 1)->where('currency_id', $currency_id)->count();
    //     return $accounts_count > 0;
    // }.

    public function image()
    {
        return $this->morphOne(File::class, 'attachable');
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
        $query->when($request->delivering_type &&  $request->party_id, function ($q) use ($request) {
            $q
                ->where('sender_party_id', $request->party_id)
                ->orWhere('receiver_party_id', $request->party_id)
                ->orWhere('office_id', $request->party_id)
                ->fromSub(function ($qq) use ($request) {
                    $qq->select('*')
                        ->from('transfers')
                        ->whereIn('delivering_type', $request->delivering_type);
                }, 'agg_tabel');
        });
        $query->when($request->type != null, function ($query, $type) use ($request) {
            $query->where('type', $request->type);
        });
        $query->when($request->status != null, function ($query, $status) use ($request) {
            $query->where('status', $request->status);
        });
        $query->when($request->transfer_id, function ($query, $id) {
            $query->where('id', $id);
        });
        // $query->when($request->party_id, function ($query, $party_id) {
        //     $query
        //         ->where('sender_party_id', $party_id)
        //         ->orWhere('receiver_party_id', $party_id)
        //         ->orWhere('office_id', $party_id);
        // });
        $query->when($request->delivering_type, function ($query, $delivering_type) {
            $query->whereIn('delivering_type', $delivering_type);
        });
        $query->when($request->from && $request->to, function ($query, $from) use ($request) {
            $query->where(DB::raw('issued_at::date'), '>=', $request->from)
                ->where(DB::raw('issued_at::date'), '<=', $request->to);
        });
        $query->when($request->user_id, function ($q, $user_id) {
            if (auth()->user()['role'] != 1) {
                $q->where('user_id',   $user_id);
            }
        });
    }
    public function get_user_account_id($currency_id)
    {
        $user = auth()->user();
        $id =  $user->accounts()
            ->where('status', 1)
            ->where('main', true)
            ->where('accounts.currency_id', $currency_id)->first(['accounts.id'])->id;
        return $id;
    }
    //0595206804
    public function getTypeStatement()
    {
        $statement = "transfer";
        if ($this->type == 0)  $statement = "outcomming $statement";
        if ($this->type == 1)  $statement = "incoming $statement";
        if ($this->delivering_type == 2) $statement = "$statement moneygram";
        if ($this->delivering_type == 3) $statement = "$statement on account";
        return $statement;
    }
    public static   function pdf_translated_headers()
    {
        return [
            __("id"),
            __("issued_at"),
            __("status"),
            __("sender_name"),
            __("to_send_amount"),
            __("delivery_currency"),
            __("receiver_name"),
            __("received_amount"),
            __("received_currency"),

        ];
    }
    public static $pdf_headers =
    [
        "id",
        "issued_at",
        "transfer_status",
        "sender_name",
        "to_send_amount",
        "delivery_currency_name",
        "receiver_name",
        "received_amount",
        "received_currency_name",

    ];

    public  function  pdf_title()
    {
        return $this->getTypeStatement();
    }

    public function dispose()
    {

        $old_entry = $this->entries()->orderBy('id', 'desc')->first();
        try {
            DB::beginTransaction();
            $entry = $this->entry()->create([
                'user_id' => request('user_id'),
                'date' => Carbon::now()->timezone('Asia/Gaza')->toDateTimeString(),
                'status' => 1,
                'document_sub_type' => 1,
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
