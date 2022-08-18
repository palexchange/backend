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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->float('amount')->default(0);
            $table->foreignId('main_currency_id')->references('id')->on('currencies');
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->float('factor')->default(1);
            $table->foreignId('beneficiary_id')->references('id')->on('parties');
            $table->integer('number')->nullable();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
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
        Schema::dropIfExists('receipts');
    }
};
