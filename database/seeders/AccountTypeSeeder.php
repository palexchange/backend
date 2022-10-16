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
        AccountType::create(['name' => 'زبون']);
        AccountType::create(['name' => 'مكتب']);
        AccountType::create(['name' => 'صندوق']);
        AccountType::create(['name' => 'خزينة']);
        AccountType::create(['name' => 'ايرادات']);
    }
}
