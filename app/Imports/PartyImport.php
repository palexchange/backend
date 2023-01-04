<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Entry;
use App\Models\EntryTransaction;
use App\Models\Party;
use App\Models\Setting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PartyImport implements ToModel, WithStartRow
{
    public $who = 1;
    public $counter = 0;

    public function __construct($who)
    {
        $this->who = $who;
    }

    public function startRow(): int
    {
        return 2;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if ($row[0]) {
            $kazem_doller_id = $this->who == 1 ?  11 : 25;
            $kazem_shikle_id = $this->who == 1 ? 12 : 26;
            $kazem_denar_id = $this->who == 1 ? 13 : 27;
            $kazem_pond_id = $this->who == 1 ? 17 : 31;
            $kazem_pond_id = $this->who == 1 ? 17 : 31;
            $money = $this->who == 1 ? 17 : 31;
            $moneygram_account_id  = Setting::find('moneygram_account')?->value;

            if ($row[0] == 'money_gram') {
                $entry = Entry::create([
                    'user_id' => $this->who + 1,
                    'date' => Carbon::now()->toDateString(),
                    'status' => 1,
                    'document_sub_type' => $row[2]  > 0 ? 5 : 4,
                    'statement' => " موني غرام 'ترصيد'",
                    // 'ref_currency_id' => $this->reference_currency_id,
                ]);
                EntryTransaction::create([
                    'entry_id' => $entry->id,
                    'debtor' => !($row[3] > 0) ? abs($row[3]) : 0,
                    'creditor' => $row[3] > 0 ? abs($row[3]) : 0,
                    'account_id' => $moneygram_account_id,
                    'exchange_rate' => 1,
                    'currency_id' => 1,   //,$this->received_currency_id,
                    'ac_debtor' => !($row[3] > 0) ? abs($row[3]) / 1 : 0,
                    'ac_creditor' => $row[3] > 0 ? abs($row[3]) / 1 : 0,
                    'transaction_type' => $row[3] > 0 ? 1 : 0,
                ]);
            } else {


                $name = $this->who == 1 ? "$row[0]  ' كاظم'" : "$row[0]  ' احمد'";

                $account = Account::create(['name' => "$name", 'type_id' => $row[1] ?? 1]);
                Party::create(['name' => "$name", 'account_id' => $account->id, "type" => $row[1] ?? 1]);



                if ($row[2] != 0) {
                    $entry = Entry::create([
                        'user_id' => $this->who + 1,
                        'date' => Carbon::now()->toDateString(),
                        'status' => 1,
                        'document_sub_type' => $row[2]  > 0 ? 5 : 4,
                        'statement' => " $name 'ترصيد'",
                        // 'ref_currency_id' => $this->reference_currency_id,
                    ]);
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[2] > 0) ? abs($row[2]) : 0,
                        'creditor' => $row[2] > 0 ? abs($row[2]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 3.47,
                        'currency_id' => 2,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[2] > 0) ? abs($row[2]) / 3.47 : 0,
                        'ac_creditor' => $row[2] > 0 ? abs($row[2]) / 3.47 : 0,
                        'transaction_type' => $row[2] > 0 ? 1 : 0,
                    ]);
                    // EntryTransaction::create([
                    //     'entry_id' => $entry->id,
                    //     'debtor' => ($row[2] > 0) ? abs($row[2]) : 0,
                    //     'creditor' => !($row[2] > 0) ? abs($row[2]) : 0,
                    //     'account_id' =>  $kazem_shikle_id,
                    //     'exchange_rate' => 3.47,
                    //     'currency_id' => 2,   //,$this->received_currency_id,
                    //     'ac_debtor' => ($row[2] > 0) ? abs($row[2]) / 3.47 : 0,
                    //     'ac_creditor' => !($row[2] > 0) ? abs($row[2]) / 3.47 : 0,
                    //     'transaction_type' => !($row[2] > 0) ? 1 : 0,
                    // ]);
                }
                if ($row[3] != 0) {
                    if ($this->who == 2) {
                        $this->counter++;
                        logger($row);
                        logger($this->counter);
                    }
                    $entry = Entry::create([
                        'user_id' => $this->who + 1,
                        'date' => Carbon::now()->toDateString(),
                        'status' => 1,
                        'document_sub_type' => $row[2]  > 0 ? 5 : 4,
                        'statement' => " $name 'ترصيد'",
                        // 'ref_currency_id' => $this->reference_currency_id,
                    ]);
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[3] > 0) ? abs($row[3]) : 0,
                        'creditor' => $row[3] > 0 ? abs($row[3]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 1,
                        'currency_id' => 1,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[3] > 0) ? abs($row[3]) : 0,
                        'ac_creditor' => $row[3] > 0 ? abs($row[3]) : 0,
                        'transaction_type' => $row[3] > 0 ? 1 : 0,
                    ]);
                    // EntryTransaction::create([
                    //     'entry_id' => $entry->id,
                    //     'debtor' => ($row[3] > 0) ? abs($row[3]) : 0,
                    //     'creditor' => !($row[3] > 0) ? abs($row[3]) : 0,
                    //     'account_id' =>  $kazem_doller_id,
                    //     'exchange_rate' => 1,
                    //     'currency_id' => 1,   //,$this->received_currency_id,
                    //     'ac_debtor' => ($row[3] > 0) ? abs($row[3]) : 0,
                    //     'ac_creditor' => !($row[3] > 0) ? abs($row[3]) : 0,
                    //     'transaction_type' => !($row[3] > 0) ? 1 : 0,
                    // ]);
                }
                if ($row[4] != 0) {
                    $entry = Entry::create([
                        'user_id' => $this->who + 1,
                        'date' => Carbon::now()->toDateString(),
                        'status' => 1,
                        'document_sub_type' => $row[2]  > 0 ? 5 : 4,
                        'statement' => " $name 'ترصيد'",
                        // 'ref_currency_id' => $this->reference_currency_id,
                    ]);
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[4] > 0) ? abs($row[4]) : 0,
                        'creditor' => $row[4] > 0 ? abs($row[4]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 0.71,
                        'currency_id' => 3,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[4] > 0) ? abs($row[4]) / 0.71 : 0,
                        'ac_creditor' => $row[4] > 0 ? abs($row[4]) / 0.71 : 0,
                        'transaction_type' => $row[4] > 0 ? 1 : 0,
                    ]);
                    // EntryTransaction::create([
                    //     'entry_id' => $entry->id,
                    //     'debtor' => ($row[4] > 0) ? abs($row[4]) : 0,
                    //     'creditor' => !($row[4] > 0) ? abs($row[4]) : 0,
                    //     'account_id' =>  $kazem_denar_id,
                    //     'exchange_rate' => 0.71,
                    //     'currency_id' => 3,   //,$this->received_currency_id,
                    //     'ac_debtor' => ($row[4] > 0) ? abs($row[4]) / 0.71 : 0,
                    //     'ac_creditor' => !($row[4] > 0) ? abs($row[4]) / 0.71 : 0,
                    //     'transaction_type' => !($row[4] > 0) ? 1 : 0,
                    // ]);
                }
                if (isset($row[5])) {
                    if (gettype($row[5]) != 'integer') return;
                    logger($row);

                    $entry = Entry::create([
                        'user_id' => $this->who + 1,
                        'date' => Carbon::now()->toDateString(),
                        'status' => 1,
                        'document_sub_type' => $row[2]  > 0 ? 5 : 4,
                        'statement' => " $name 'ترصيد'",
                        // 'ref_currency_id' => $this->reference_currency_id,
                    ]);

                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[5] > 0) ? abs($row[5]) : 0,
                        'creditor' => $row[5] > 0 ? abs($row[5]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 21.419753,
                        'currency_id' => 7,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[5] > 0) ? abs($row[5]) / 21.419753 : 0,
                        'ac_creditor' => $row[5] > 0 ? abs($row[5]) / 21.419753 : 0,
                        'transaction_type' => $row[5] > 0 ? 1 : 0,
                    ]);
                    // EntryTransaction::create([
                    //     'entry_id' => $entry->id,
                    //     'debtor' => ($row[5] > 0) ? abs($row[5]) : 0,
                    //     'creditor' => !($row[5] > 0) ? abs($row[5]) : 0,
                    //     'account_id' =>  $kazem_pond_id,
                    //     'exchange_rate' => 21.419753,
                    //     'currency_id' => 7,   //,$this->received_currency_id,
                    //     'ac_debtor' => ($row[5] > 0) ? abs($row[5]) / 21.419753 : 0,
                    //     'ac_creditor' => !($row[5] > 0) ? abs($row[5]) / 21.419753 : 0,
                    //     'transaction_type' => !($row[5] > 0) ? 1 : 0,
                    // ]);
                }
            }
        }
    }
}
