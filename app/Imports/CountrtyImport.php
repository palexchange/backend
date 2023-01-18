<?php

namespace App\Imports;

use App\Models\country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithLimit;

class CountrtyImport implements ToModel, WithStartRow, WithLimit
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function limit(): int
    {
        return 10;
    }
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        // return new country([
        //     'name_ar' => $row[0],
        //     'name_en' => $row[1],
        // ]);
    }
}
