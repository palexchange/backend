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
            $table->foreignId('from_account_id')->references('id')->on('accounts');
            $table->foreignId('to_account_id')->references('id')->on('accounts');
            $table->float('from_amount')->default(0);
            $table->float('exchange_rate')->default(1);
            $table->float('to_amount')->default(0);
            $table->integer('status')->default(0);
            $table->foreignId('currency_id')->nullable()->references('id')->on('currencies');
            $table->text('statement')->nullable();
            // $table->float('factor')->default(1);
            // $table->foreignId('beneficiary_id')->references('id')->on('parties');
            // $table->integer('number')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->boolean('is_expenses')->default(false);
            $table->foreignId('expenses_account_id')->references('id')->on('accounts');
            $table->integer('type')->index('type'); // 1 مقبوضاتinputs  2 مسحوباتoutputs 3 اتزان حسابbalanceing account
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
