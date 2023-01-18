<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Country;
use App\Models\Party;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;


class money_gram_parties implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        if ($row[0]) {
            $account = Account::UpdateOrcreate(['name' => $row[0]], ['name' => $row[0], 'type_id' => 1]);
            Party::UpdateOrcreate(['name' => $row[0]], [
                'name' => $row[0],
                'id_no' => $row[1],
                'phone' => $row[2],
                'address' => $row[3],
                'country_id' => $row[4],
                'account_id' => $account->id,
            ]);
        }
    }
}
