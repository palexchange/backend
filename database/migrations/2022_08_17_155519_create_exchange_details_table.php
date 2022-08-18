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
            $table->foreignId('exchange_id')->references('id')->on('exchanges');
            $table->float('amount')->default(0);
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->float('factor')->default(1);
            $table->float('amount_after')->default(0);
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
