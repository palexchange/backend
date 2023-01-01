<?php

namespace Database\Seeders;

use App\Imports\PartyImport;
use App\Imports\TransfersPartyImport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

// use database\seeders\StockSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            AccountSeeder::class,
            AccountTypeSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            StockSeeder::class,
            PartySeeder::class,
            SettingSeeder::class,
            // TransferSeeder::class,
            // ExchangeSeeder::class
        ]);
        Excel::import(new PartyImport(1), base_path() . '/excel/k_parties.xlsx');
        Excel::import(new PartyImport(2), base_path() . '/excel/ah_parties.xlsx');
    }
}
