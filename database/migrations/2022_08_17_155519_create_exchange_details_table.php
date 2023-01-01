<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->references('id')->on('exchanges')->onDelete('cascade');
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->float('amount', 18, 5)->default(0);
            $table->float('exchange_rate')->default(1);
            $table->float('usd_factor')->default(1);
            $table->integer('type'); // 1 from ,, 2 to
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_details');
    }
};
