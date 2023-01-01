<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountType::create(['name' => 'زبون']); //1
        AccountType::create(['name' => 'مورد']); // 2
        AccountType::create(['name' => 'صندوق']); // 3
        AccountType::create(['name' => 'خزينة']); // 4
        AccountType::create(['name' => 'ايرادات']); // 5
        AccountType::create(['name' => 'وسيط']); // 6
        AccountType::create(['name' => 'مصروفات']); // 7
        // AccountType::create(['name' => 'محفظة']); // 7
    }
}
