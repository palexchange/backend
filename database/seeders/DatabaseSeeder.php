<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
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
            SettingSeeder::class,
            TransferSeeder::class,
            ExchangeSeeder::class
        ]);
    }
}
