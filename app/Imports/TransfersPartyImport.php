<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Entry;
use App\Models\EntryTransaction;
use App\Models\Party;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class TransfersPartyImport implements ToModel, WithStartRow
{


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
            $ahmad_doller_id = 25;
            $ahmad_shikle_id = 26;
            $ahmad_denar_id = 27;
            $account = Account::create(['name' => "$row[0] 'أحمد'", 'type_id' => 1]);
            Party::create(['name' => "$row[0] 'أحمد'", 'account_id' => $account->id, "type" => $row[1] ?? 1]);
            if ($row[0]) {
                $entry = Entry::create([
                    'user_id' => request('user_id'),
                    'date' => Carbon::now()->toDateString(),
                    'status' => 1,
                    'document_sub_type' => 2,
                    'statement' => " $row[0] 'ترصيد'",
                    // 'ref_currency_id' => $this->reference_currency_id,
                ]);

                if ($row[2] != 0) {
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[2] > 0) ? abs($row[2]) : 0,
                        'creditor' => $row[2] > 0 ? abs($row[2]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 1,
                        'currency_id' => 2,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[2] > 0) ? abs($row[2]) : 0,
                        'ac_creditor' => $row[2] > 0 ? abs($row[2]) : 0,
                        'transaction_type' => $row[2] > 0 ? 1 : 0,
                    ]);
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => ($row[2] > 0) ? abs($row[2]) : 0,
                        'creditor' => !($row[2] > 0) ? abs($row[2]) : 0,
                        'account_id' =>  $ahmad_shikle_id,
                        'exchange_rate' => 1,
                        'currency_id' => 2,   //,$this->received_currency_id,
                        'ac_debtor' => ($row[2] > 0) ? abs($row[2]) : 0,
                        'ac_creditor' => !($row[2] > 0) ? abs($row[2]) : 0,
                        'transaction_type' => !($row[2] > 0) ? 1 : 0,
                    ]);
                }
                if ($row[3] != 0) {
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
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => ($row[3] > 0) ? abs($row[3]) : 0,
                        'creditor' => !($row[3] > 0) ? abs($row[3]) : 0,
                        'account_id' =>  $ahmad_doller_id,
                        'exchange_rate' => 1,
                        'currency_id' => 1,   //,$this->received_currency_id,
                        'ac_debtor' => ($row[3] > 0) ? abs($row[3]) : 0,
                        'ac_creditor' => !($row[3] > 0) ? abs($row[3]) : 0,
                        'transaction_type' => !($row[3] > 0) ? 1 : 0,
                    ]);
                }
                if ($row[4] != 0) {
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => !($row[4] > 0) ? abs($row[4]) : 0,
                        'creditor' => $row[4] > 0 ? abs($row[4]) : 0,
                        'account_id' => $account->id,
                        'exchange_rate' => 1,
                        'currency_id' => 3,   //,$this->received_currency_id,
                        'ac_debtor' => !($row[4] > 0) ? abs($row[4]) : 0,
                        'ac_creditor' => $row[4] > 0 ? abs($row[4]) : 0,
                        'transaction_type' => $row[4] > 0 ? 1 : 0,
                    ]);
                    EntryTransaction::create([
                        'entry_id' => $entry->id,
                        'debtor' => ($row[4] > 0) ? abs($row[4]) : 0,
                        'creditor' => !($row[4] > 0) ? abs($row[4]) : 0,
                        'account_id' =>  $ahmad_denar_id,
                        'exchange_rate' => 1,
                        'currency_id' => 3,   //,$this->received_currency_id,
                        'ac_debtor' => ($row[4] > 0) ? abs($row[4]) : 0,
                        'ac_creditor' => !($row[4] > 0) ? abs($row[4]) : 0,
                        'transaction_type' => !($row[4] > 0) ? 1 : 0,
                    ]);
                }
            }
        }
    }
}
