<?php

namespace Database\Seeders;

use App\Imports\PartyImport;
use App\Models\Account;
use App\Models\Party;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

use function PHPSTORM_META\type;

class PartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $account = Account::create(['name' => 'زبون عام', 'type_id' => 1]);
        Party::create(['name' => 'زبون عام', 'account_id' => $account->id]);
        $account = Account::create(['name' => 'موني غرام', 'type_id' => 2]);
        Party::create(['name' => 'موني غرام', 'account_id' => $account->id]);
    }
}
