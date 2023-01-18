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
    public function rules(): array
    {
        return [
            'name' => 'distinct',
        ];
    }
    public function model(array $row)
    {
        if ($row[0]) {
            $old_ = Party::where('name', $row[0]);
            if (isset($old_->id)) {
                $account = Account::create(['name' => $row[0], 'type_id' => 1]);
                Party::create([
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
}
