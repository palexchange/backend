<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Entry;
use App\Models\EntryTransaction;
use App\Models\Party;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class TransfersPartyImport implements ToModel, WithStartRow
{
    public $who = 1;

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

        $entry_1 = Entry::create([
            'user_id' => 2,
            'date' => Carbon::now()->toDateString(),
            'status' => 1,
            'document_sub_type' => $row[2]  > 0 ? 5 : 4,
            'statement' => "ترصيد صناديق",
            // 'ref_currency_id' => $this->reference_currency_id,
        ]);
        EntryTransaction::create([
            'entry_id' => $entry_1->id,
            'debtor' => ($row[3] > 0) ? abs($row[3]) : 0,
            'creditor' => !($row[3] > 0) ? abs($row[3]) : 0,
            'account_id' =>  $row[5],
            'exchange_rate' => $row[2],
            'currency_id' => $row[1],   //,$this->received_currency_id,
            'ac_debtor' => ($row[3] > 0) ? abs($row[3]) / $row[2] : 0,
            'ac_creditor' => !($row[3] > 0) ? abs($row[3]) / $row[2] : 0,
            'transaction_type' => !($row[3] > 0) ? 1 : 0,
        ]);

        $entry_2 = Entry::create([
            'user_id' => 3,
            'date' => Carbon::now()->toDateString(),
            'status' => 1,
            'document_sub_type' => $row[2]  > 0 ? 5 : 4,
            'statement' => "ترصيد صناديق",
            // 'ref_currency_id' => $this->reference_currency_id,
        ]);
        EntryTransaction::create([
            'entry_id' => $entry_2->id,
            'debtor' => ($row[4] > 0) ? abs($row[4]) : 0,
            'creditor' => !($row[4] > 0) ? abs($row[4]) : 0,
            'account_id' => $row[6],
            'exchange_rate' => $row[2],
            'currency_id' => $row[1],   //,$this->received_currency_id,
            'ac_debtor' => ($row[4] > 0) ? abs($row[4]) / $row[2] : 0,
            'ac_creditor' => !($row[4] > 0) ? abs($row[4]) / $row[2] : 0,
            'transaction_type' => !($row[4] > 0) ? 1 : 0,
        ]);
    }
}
