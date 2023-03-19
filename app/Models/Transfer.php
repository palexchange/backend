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
            if ($this->delivering_type == 2) {
                $this->logIncomingMoneyGramAmount($entry)->handleCommision();
            } else {
                $this->logIncomingAmount($entry)->handleCommision();
            }
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
                    'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
                    'ac_creditor' =>  $transaction['ac_creditor'],
                    'transaction_type' => $transaction['transaction_type'],
                ]);
            };
        } else {
            if ($this->delivering_type == 2) {
                $this->logMoneyGramAmount($entry)->handleCommision();
            } else {
                $this->logAmount($entry)->handleCommision();
            }

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
                    'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
                    'transaction_type' => $transaction['transaction_type'],
                ]);
            };
        }
    }

    public function logMoneyGramAmount($entry)
    {

        $transfers_commission_account_id = Setting::find('transfers_commission_account_id')?->value;
        $transactions = [];

        $sender_amount =    $this->final_received_amount; // $this->final_received_amount;
        $account_id =   $this->user_account_id; // $entry->ref_currency->account_id;
        // transfer amount
        $transactions[] = [
            'account_id' => $account_id, // $this->user_account_id
            'amount' => $sender_amount,
            'ac_amount' => $sender_amount * $this->exchange_rate_to_delivery_currency,
            'transaction_type' => $this->delivering_type == 3 ? 1 : 6, // 6 not include to net_balance
            'exchange_rate' => $this->exchange_rate_to_delivery_currency,
            'currency_id' => $this->delivery_currency_id,
            'type' => 0,
        ];

        $transfer_commission_box_id = $this->get_user_account_id($this->transfer_commission_currency);
        $transfer_commission_inDoller = 0;
        if ($this->transfer_commission > 0) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $transfer_commission_inDoller = $this->toDoller($t_amount, $this->transfer_commission_currency, $this->transfer_commission_exchange_rate);
            $transfer_commission_inDoller =  $transfer_commission_inDoller;
            $transactions[] = [
                'account_id' =>  $transfer_commission_box_id,
                'amount' => $t_amount,
                'ac_amount' => $transfer_commission_inDoller,
                'transaction_type' => $this->delivering_type == 3 ? 11 : 2,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'type' =>  0,
            ];
            $transactions[] = [
                'account_id' => $transfers_commission_account_id, //$transfer_profit_account_id,
                'amount' => $t_amount,
                'ac_amount' => $transfer_commission_inDoller,
                'transaction_type' => 1,
                'on_account_balance_id' => $this->get_user_account_id($this->transfer_commission_currency),
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'type' =>  1,
            ];
        }

        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' =>   $this->office_amount,
            'ac_amount' => $this->office_amount,
            'transaction_type' => 1,
            'currency_id' => 1,
            'exchange_rate' => 1,
            'type' => 1,
        ];


        foreach ($transactions as $transaction) {
            EntryTransaction::create([
                'entry_id' => $entry->id,
                'debtor' => !$transaction['type'] ? $transaction['amount'] : 0,
                'creditor' => $transaction['type'] ? $transaction['amount'] : 0,
                'account_id' => $transaction['account_id'],
                'exchange_rate' =>  $transaction['exchange_rate'],
                'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
                'currency_id' =>  $transaction['currency_id'] ?? 1, //,$this->received_currency_id,
                'ac_debtor' => !$transaction['type'] ? ($transaction['ac_amount'] ?? $transaction['amount']) : 0,
                'ac_creditor' => $transaction['type'] ? ($transaction['ac_amount'] ?? $transaction['amount']) : 0,
                'transaction_type' => $transaction['transaction_type'],
            ]);
        }

        return $this;
    }

    public  function logAmount($entry)
    {

        $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        $office_commission_account_id = Setting::find('office_commission_account_id')?->value;
        $transfers_commission_account_id = Setting::find('transfers_commission_account_id')?->value;
        $transactions = [];

        $sender_amount =    $this->delivering_type == 2 ? $this->final_received_amount : $this->to_send_amount; // $this->final_received_amount;
        $account_id = $this->delivering_type == 3 ? $this->sender_party->account_id : $this->user_account_id; // $entry->ref_currency->account_id;
        // transfer amount
        $transactions[] = [
            'account_id' => $account_id, // $this->user_account_id
            'amount' => $sender_amount,
            'ac_amount' => $sender_amount * $this->exchange_rate_to_delivery_currency,
            'transaction_type' => $this->delivering_type == 3 ? 1 : 6, // 6 not include to net_balance
            'exchange_rate' => $this->exchange_rate_to_delivery_currency,
            'currency_id' => $this->delivery_currency_id,
            'type' => 0,
        ];

        $transfer_commission_box_id = $this->get_user_account_id($this->transfer_commission_currency);
        $transfer_commission_inDoller = 0;
        if ($this->transfer_commission > 0) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $transfer_commission_inDoller = $this->toDoller($t_amount, $this->transfer_commission_currency, $this->transfer_commission_exchange_rate);
            $transfer_commission_inDoller = round($transfer_commission_inDoller, 0);
            $transactions[] = [
                'account_id' => $this->delivering_type == 3 ? $account_id : $transfer_commission_box_id,
                'amount' => $t_amount,
                'ac_amount' => $transfer_commission_inDoller,
                'transaction_type' => $this->delivering_type == 3 ? 11 : 2,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'type' =>  0,
            ];

            $transactions[] = [
                'account_id' => $transfers_commission_account_id, //$transfer_profit_account_id,
                'amount' => $t_amount,
                'ac_amount' => $transfer_commission_inDoller,
                'transaction_type' => 1,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'on_account_balance_id' => $this->get_user_account_id($this->transfer_commission_currency),
                'type' =>  1,
            ];
        }

        $transactions[] = [
            'account_id' => $this->office->account_id,
            'amount' => $this->office_amount_in_office_currency ?? $this->office_amount,
            'ac_amount' => $this->office_amount,
            'transaction_type' => 1,
            'currency_id' => $this->office_currency_id,
            'exchange_rate' => 1 / Stock::find($this->office_currency_id)->close_mid,
            'type' => 1,
        ];

        $commission_box_id = 0;
        if ($this->office_commission > 0 || $this->returned_commission > 0) {
            $commission_box_id = $this->get_user_account_id($this->office_currency_id);
            $office_exchange_rate =  $this->office_exchange_rate_to_usd;
        }
        $office_commission_amount_usd = 0;
        if ($this->office_commission > 0 && $this->delivering_type != 2) {
            $o_amount = $this->office_commission_type == 1 ?
                $this->calcCommissionAmmount($this->office_amount_befor_commission, $this->office_commission)
                : $this->office_commission;
            $office_commission_amount_usd = $office_exchange_rate == 1 ? $office_commission_amount_usd : round($office_commission_amount_usd, 0);
            $office_commission_amount_usd = $this->office_currency_id == 4 ? $o_amount * $office_exchange_rate :  $o_amount / $office_exchange_rate;
            $transactions[] = [
                'account_id' => $office_commission_account_id,
                'ac_amount' => $office_commission_amount_usd,
                'amount' => $o_amount,
                'transaction_type' => 3,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' =>  $this->office_currency_id,
                'exchange_rate' =>  $office_exchange_rate,
                'type' =>  0,
            ];
        }
        $returned_commission_amount_usd = 0;
        if ($this->returned_commission > 0) {
            $f_amount = $this->returned_commission_type == 1 ?
                $this->calcCommissionAmmount($this->office_amount_befor_commission, $this->returned_commission)
                : $this->returned_commission;
            $returned_commission_amount_usd = $this->office_currency_id == 4 ? $f_amount * $office_exchange_rate : $f_amount / $office_exchange_rate;
            $returned_commission_amount_usd = $office_exchange_rate == 1 ? $returned_commission_amount_usd : round($returned_commission_amount_usd, 0);
            $transactions[] = [
                // 'account_id' => $transfer_commission_box_id, //$commission_box_id,
                'account_id' => $returned_commission_account_id, //$commission_box_id,
                'amount' =>  $f_amount, //$f_amount,
                'ac_amount' => $returned_commission_amount_usd,
                'transaction_type' => 4,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' =>  $this->office_currency_id,
                'exchange_rate' =>  $office_exchange_rate,
                'type' =>  1,
            ];
        }               //23            - 30 + 10 - 20
        $office_total_comission = $returned_commission_amount_usd  - $office_commission_amount_usd;
        $currency_diff = $this->profit -
            ($returned_commission_amount_usd  + $transfer_commission_inDoller - $office_commission_amount_usd);

        if (abs($office_total_comission) > 0 || $currency_diff) {
            if (abs($currency_diff) > 0) {
                $transactions[] = [
                    'account_id' => $exchange_difference_account_id,
                    'amount' => round(abs($currency_diff), 0),
                    'transaction_type' => 9,
                    'on_account_balance_id' => $this->get_user_account_id(1),
                    'exchange_rate' => 1,
                    'type' =>  $currency_diff > 0 ?  1 : 0,
                ];
            }
        }


        foreach ($transactions as $transaction) {
            EntryTransaction::create([
                'entry_id' => $entry->id,
                'debtor' => !$transaction['type'] ? $transaction['amount'] : 0,
                'creditor' => $transaction['type'] ? $transaction['amount'] : 0,
                'account_id' => $transaction['account_id'],
                'exchange_rate' =>  $transaction['exchange_rate'],
                'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
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

        // $transfer_profit_account_id = Account::find(2)->id;

        $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        $office_commission_account_id = Setting::find('office_commission_account_id')?->value;
        $transfers_commission_account_id = Setting::find('transfers_commission_account_id')?->value;

        $transactions = [];
        $user_account_id = $this->user_account_id;
        $amount =  $this->office_amount_in_office_currency == 0 ?  $this->office_amount : $this->office_amount_in_office_currency;
        $transactions[] = [
            'account_id' => $this->office->account_id, // $this->user_account_id moew moew moew moew moew
            'amount' => $this->on_dollar_account ? ($amount * $this->exchange_rate_to_office_currency) : $amount,
            'ac_amount' => $amount * $this->exchange_rate_to_office_currency,
            'transaction_type' => 1,
            'currency_id' => $this->on_dollar_account ? 1 : $this->office_currency_id,
            'exchange_rate' => $this->on_dollar_account ? 1 : (1 / $this->exchange_rate_to_office_currency),
            'type' => 0,
        ];
        if ($this->delivering_type != 4) {
            if ($this->delivering_type == 2) {
                $transactions[] = [
                    'account_id' => $user_account_id,
                    'currency_id' => $this->received_currency_id,
                    'amount' =>   $this->received_amount,
                    'exchange_rate' => $this->transfer_commission_exchange_rate,
                    'ac_amount' => $this->received_amount * $this->transfer_commission_exchange_rate,
                    'transaction_type' => 10,
                    'type' => 1,
                ];
            } else {
                $transactions[] = [
                    'account_id' =>  $user_account_id,
                    'amount' => $this->final_received_amount,
                    'ac_amount' => $this->toDoller($this->final_received_amount, $this->received_currency_id,  $this->transfer_commission_exchange_rate),
                    'currency_id' => $this->received_currency_id,
                    'exchange_rate' => $this->transfer_commission_exchange_rate, //$this->delivering_type == 2 ? 1 : number_format($this->received_amount / $this->a_received_amount, 3, '.', ""),
                    'transaction_type' =>  10,
                    'type' => 1,
                ];
            }
        }
        //office_commission
        $office_commission_amount_usd = 0;
        if ($this->office_commission > 0) {
            $o_amount = $this->office_commission_type == 1 ?
                $this->calcCommissionAmmount($this->office_amount_in_office_currency, $this->office_commission)
                : $this->office_commission;
            $ac_o_amount = round($o_amount * $this->exchange_rate_to_office_currency, 1);
            $office_commission_amount_usd = $ac_o_amount;
            $transactions[] = [
                'account_id' => $returned_commission_account_id,
                'amount' => $o_amount,
                'ac_amount' => $ac_o_amount,
                'transaction_type' => 4,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' => $this->office_currency_id,
                'exchange_rate' => 1 / $this->exchange_rate_to_office_currency,
                'type' =>  1,
            ];
        }
        //returned_commission
        $returned_commission_amount_usd = 0;
        if ($this->returned_commission > 0) {
            $returned_commission_amount_usd = round($this->returned_commission * $this->exchange_rate_to_office_currency, 1);
            $transactions[] = [
                'account_id' => $office_commission_account_id,
                'amount' => $this->returned_commission,
                'ac_amount' => $returned_commission_amount_usd, // $this->returned_commission * $this->exchange_rate_to_office_currency,
                'transaction_type' => 3,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' => $this->office_currency_id,
                'exchange_rate' => 1 / $this->exchange_rate_to_office_currency,
                'type' =>  0,
            ];
        }
        $t_amount_indoller = 0;
        //transfer_commission
        if ($this->transfer_commission > 0 && $this->delivering_type == 2) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $t_amount_indoller = $t_amount;
            $transactions[] = [
                'account_id' => $transfers_commission_account_id,
                'amount' => $t_amount,
                'ac_amount' => $t_amount,
                'on_account_balance_id' => $this->get_user_account_id(1),
                'transaction_type' => 2,
                'currency_id' => 1,
                'exchange_rate' => 1,
                'type' =>  1,
            ];
        }

        if ($this->transfer_commission > 0 && $this->delivering_type != 2) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $t_amount_indoller = $this->toDoller($t_amount, $this->received_currency_id, $this->transfer_commission_exchange_rate);
            $transactions[] = [
                'account_id' => $transfers_commission_account_id,
                'amount' => $t_amount,
                'ac_amount' => $t_amount_indoller,
                'transaction_type' => 2,
                'on_account_balance_id' => $this->get_user_account_id($this->received_currency_id),
                'currency_id' => $this->received_currency_id,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'type' =>  1,
            ];
        }

        $office_total_comission = $office_commission_amount_usd - $returned_commission_amount_usd;
        $office_total_comission  = round($office_total_comission, 1);
        $currency_diff = $this->profit - $office_total_comission - $t_amount_indoller;
        if (abs($currency_diff) > 0) {
            $transactions[] = [
                'account_id' => $exchange_difference_account_id,
                'amount' => abs($currency_diff),
                'transaction_type' => 9,
                'exchange_rate' => 1,
                'type' => $currency_diff > 0 ?  1 : 0,
            ];
        }

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
                'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
                'currency_id' =>  $transaction['currency_id'] ?? 1, //,$this->received_currency_id,
                'ac_debtor' => !$transaction['type'] ?  (isset($transaction['ac_amount']) ? $transaction['ac_amount'] : $transaction['amount']) : 0,
                'ac_creditor' => $transaction['type'] ? (isset($transaction['ac_amount']) ? $transaction['ac_amount'] : $transaction['amount']) : 0,
                'transaction_type' => $transaction['transaction_type'],
            ]);
        }
        return $this;
    }
    public  function logIncomingMoneyGramAmount($entry)
    {

        // $transfer_profit_account_id = Account::find(2)->id;

        $exchange_difference_account_id = Setting::find('exchange_difference_account_id')?->value;
        $returned_commission_account_id = Setting::find('returned_commission_account_id')?->value;
        $office_commission_account_id = Setting::find('office_commission_account_id')?->value;
        $transfers_commission_account_id = Setting::find('transfers_commission_account_id')?->value;

        $transactions = [];
        $user_account_id = $this->user_account_id;
        $amount =  $this->office_amount_in_office_currency == 0 ?  $this->office_amount : $this->office_amount_in_office_currency;
        $transactions[] = [
            'account_id' => $this->office->account_id, // $this->user_account_id moew moew moew moew moew
            'amount' => $this->on_dollar_account ? ($amount * $this->exchange_rate_to_office_currency) : $amount,
            'ac_amount' => $amount * $this->exchange_rate_to_office_currency,
            'transaction_type' => 1,
            'currency_id' => $this->on_dollar_account ? 1 : $this->office_currency_id,
            'exchange_rate' => $this->on_dollar_account ? 1 : (1 / $this->exchange_rate_to_office_currency),
            'type' => 0,
        ];
        if ($this->delivering_type != 4) {
            if ($this->delivering_type == 2) {
                $transactions[] = [
                    'account_id' => $user_account_id,
                    'currency_id' => $this->received_currency_id,
                    'amount' =>   $this->received_amount,
                    'exchange_rate' => $this->transfer_commission_exchange_rate,
                    'ac_amount' => $this->received_amount * $this->transfer_commission_exchange_rate,
                    'transaction_type' => 10,
                    'type' => 1,
                ];
            } else {
                $transactions[] = [
                    'account_id' =>  $user_account_id,
                    'amount' => $this->final_received_amount,
                    'ac_amount' => $this->toDoller($this->final_received_amount, $this->received_currency_id,  $this->transfer_commission_exchange_rate),
                    'currency_id' => $this->received_currency_id || 1,
                    'exchange_rate' => $this->transfer_commission_exchange_rate || 1, //$this->delivering_type == 2 ? 1 : number_format($this->received_amount / $this->a_received_amount, 3, '.', ""),
                    'transaction_type' =>  10,
                    'type' => 1,
                ];
            }
        }
        //office_commission
        $office_commission_amount_usd = 0;
        if ($this->office_commission > 0) {
            $o_amount = $this->office_commission_type == 1 ?
                $this->calcCommissionAmmount($this->office_amount_in_office_currency, $this->office_commission)
                : $this->office_commission;
            $ac_o_amount = round($o_amount * $this->exchange_rate_to_office_currency, 1);
            $office_commission_amount_usd = $ac_o_amount;
            $transactions[] = [
                'account_id' => $returned_commission_account_id,
                'amount' => $o_amount,
                'ac_amount' => $ac_o_amount,
                'transaction_type' => 4,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' => $this->office_currency_id,
                'exchange_rate' => 1 / $this->exchange_rate_to_office_currency,
                'type' =>  1,
            ];
        }
        //returned_commission
        $returned_commission_amount_usd = 0;
        if ($this->returned_commission > 0) {
            logger("this->returned_commission");
            logger($this->returned_commission);

            $returned_commission_amount_usd = round($this->returned_commission * $this->exchange_rate_to_office_currency, 1);
            $transactions[] = [
                'account_id' => $office_commission_account_id,
                'amount' => $this->returned_commission,
                'ac_amount' => $returned_commission_amount_usd, // $this->returned_commission * $this->exchange_rate_to_office_currency,
                'transaction_type' => 3,
                'on_account_balance_id' => $this->get_user_account_id($this->office_currency_id),
                'currency_id' => $this->office_currency_id,
                'exchange_rate' => 1 / $this->exchange_rate_to_office_currency,
                'type' =>  0,
            ];
        }
        $t_amount_indoller = 0;
        //transfer_commission
        if ($this->transfer_commission > 0 && $this->delivering_type == 2) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $t_amount_indoller = $t_amount;
            $transactions[] = [
                'account_id' => $transfers_commission_account_id,
                'amount' => $t_amount,
                'ac_amount' => $t_amount,
                'transaction_type' => 2,
                'on_account_balance_id' => $this->get_user_account_id(1),
                'currency_id' => 1,
                'exchange_rate' => 1,
                'type' =>  1,
            ];
        }

        if ($this->transfer_commission > 0 && $this->delivering_type != 2) {
            $t_amount = $this->is_commission_percentage == 1 ?
                $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                : $this->transfer_commission;
            $t_amount_indoller = $this->toDoller($t_amount, $this->received_currency_id, $this->transfer_commission_exchange_rate);
            $transactions[] = [
                'account_id' => $transfers_commission_account_id,
                'amount' => $t_amount,
                'ac_amount' => $t_amount_indoller,
                'transaction_type' => 2,
                'on_account_balance_id' => $this->get_user_account_id($this->received_currency_id),
                'currency_id' => $this->received_currency_id,
                'exchange_rate' => $this->transfer_commission_exchange_rate || 1,
                'type' =>  1,
            ];
        }

        $office_total_comission = $office_commission_amount_usd - $returned_commission_amount_usd;
        $office_total_comission  = round($office_total_comission, 1);
        $currency_diff = $this->profit - $office_total_comission - $t_amount_indoller;
        if (abs($currency_diff) > 0) {
            $transactions[] = [
                'account_id' => $exchange_difference_account_id,
                'amount' => abs($currency_diff),
                'transaction_type' => 9,
                'exchange_rate' => 1,
                'on_account_balance_id' => $this->get_user_account_id(1),
                'type' => $currency_diff > 0 ?  1 : 0,
            ];
        }

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
                'on_account_balance_id' =>  isset($transaction['on_account_balance_id']) ? $transaction['on_account_balance_id'] : null,
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


            $exchange_rate = $this->a_received_amount_exchange_rate;
            // if ($this->delivering_type != 2) {
            //     $exchange_rate = $this->exchange_rate_to_office_currency;
            // }
            if ($this->delivering_type == 4) {
                $trans[] = [
                    'account_id' => $this->receiver_party->account_id,
                    'exchange_rate' => $exchange_rate,
                    'currency_id' => $this->received_currency_id,
                    'creditor' =>     $this->final_received_amount,
                    'ac_creditor' => $this->final_received_amount /  $exchange_rate,
                    'debtor' => 0,
                    'ac_debtor' => 0,
                    'transaction_type' => 1,
                ];
            } else {

                $trans[] = [
                    'account_id' => $this->receiver_party->account_id,
                    'exchange_rate' => $exchange_rate,
                    'currency_id' => $this->received_currency_id,
                    'debtor' =>  0,
                    'ac_debtor' => 0,
                    'creditor' =>   $this->final_received_amount, // $this->to_send_amount * $exchange_rate,
                    'ac_creditor' => $this->final_received_amount / $exchange_rate, //$this->to_send_amount,
                    'transaction_type' => 0,
                ];
            }
            if ($this->delivering_type != 4) {

                $trans[] = [
                    'account_id' => $this->receiver_party->account_id,
                    'exchange_rate' => $exchange_rate,
                    'currency_id' => $this->received_currency_id,
                    'debtor' =>   $this->final_received_amount,
                    'ac_debtor' => $this->final_received_amount / $exchange_rate,
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
            //add commission as transaction
            if ($this->transfer_commission > 0) {
                $t_amount = $this->is_commission_percentage == 1 ?
                    $this->calcCommissionAmmount($this->to_send_amount, $this->transfer_commission)
                    : $this->transfer_commission;
                $transfer_commission_inDoller = $this->toDoller($t_amount, $this->transfer_commission_currency, $this->transfer_commission_exchange_rate);
                $trans[] = [
                    'account_id' => $this->sender_party->account_id,
                    'debtor' =>  $t_amount,
                    'ac_debtor' => $transfer_commission_inDoller,
                    'creditor' => 0,
                    'ac_creditor' =>  0,
                    'transaction_type' => 2,
                    'exchange_rate' => $this->transfer_commission_exchange_rate,
                    'currency_id' => $this->transfer_commission_currency,
                ];
                $trans[] = [
                    'account_id' => $this->sender_party->account_id,
                    'debtor' => 0,
                    'ac_debtor' => 0,
                    'creditor' => $t_amount,
                    'ac_creditor' =>  $transfer_commission_inDoller,
                    'transaction_type' => 2,
                    'exchange_rate' => $this->transfer_commission_exchange_rate,
                    'currency_id' => $this->transfer_commission_currency,

                ];
            }

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


        /////   receiver  transactions
        if ($this->delivering_type == 2) {
            $transfer_commission_inDoller = $this->toDoller($this->transfer_commission, $this->transfer_commission_currency, $this->transfer_commission_exchange_rate);
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>    $this->transfer_commission,
                'ac_creditor' =>  $transfer_commission_inDoller,
                'transaction_type' => 0,
            ];
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->transfer_commission_exchange_rate,
                'currency_id' => $this->transfer_commission_currency,
                'debtor' =>    $this->transfer_commission,
                'ac_debtor' =>  $transfer_commission_inDoller,
                'creditor' => 0,
                'ac_creditor' => 0,
                'transaction_type' => 1,
            ];
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>  0,
                'ac_debtor' => 0,
                'creditor' =>   $this->final_received_amount,
                'ac_creditor' => $this->final_received_amount,
                'transaction_type' => 0,
            ];
            $trans[] = [
                'account_id' => $this->sender_party->account_id,
                'exchange_rate' => $this->exchange_rate_to_reference_currency,
                'currency_id' => $this->received_currency_id,
                'debtor' =>   $this->final_received_amount,
                'ac_debtor' => $this->final_received_amount,
                'creditor' => 0,
                'ac_creditor' => 0,
                'transaction_type' => 1,
            ];
        } else {
            if ($this->receiver_party->account_id != $this->sender_party->account_id) {
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
            return number_format(($this->to_send_amount * $this->exchange_rate_to_office_currency) -
                $this->a_received_amount -
                ($this->returned_commission * $this->exchange_rate_to_office_currency) +
                ($this->office_commission * $this->exchange_rate_to_office_currency), 3, '.', "");
        }
    }
    public function ExchangeDiffernce()
    {
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
        $query->when($request->sender_party_id, function ($query, $party_id) {
            $query->where('sender_party_id', $party_id);
        });
        $query->when($request->receiver_party_id, function ($query, $party_id) {
            $query->where('receiver_party_id', $party_id);
        });
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
        logger("currency_id");
        logger($currency_id);
        logger("currency_id");
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


    public function calcCommissionAmmount($amount,  $percentage)
    {
        return $amount * $percentage / 100;
    }
    public function toDoller($amount, $curr, $factor)
    {
        $factor = $factor == 0 ? 1 : $factor;
        return $curr == 4 ? $amount * $factor : $amount / $factor;
    }
}
