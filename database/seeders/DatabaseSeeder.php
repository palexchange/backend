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
        User::create(['email' => 'test@test.com', 'password' => Hash::make('a123'), 'name' => 'test']);
        $this->call([
            CurrencySeeder::class,
            CountrySeeder::class,
            StockSeeder::class,
            SettingSeeder::class,
            TransferSeeder::class,
            ExchangeSeeder::class
        ]);
    }
}