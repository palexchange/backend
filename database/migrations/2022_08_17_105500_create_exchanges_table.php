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
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->float('amount')->default(0);
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->foreignId('beneficiary_id')->references('id')->on('parties');
            $table->integer('number')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('entry_id')->nullable()->references('id')->on('entries');
            $table->foreignId('reference_currency_id')->references('id')->on('currencies');
            $table->float('exchange_rate')->default(1);
            $table->float('amount_after')->default(0);
            $table->float('profit')->default(0);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.غا
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchanges');
    }
};
