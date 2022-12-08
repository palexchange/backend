<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function PHPUnit\Framework\once;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->references('id')->on('entries');
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->float('debtor')->default(0);
            $table->float('creditor')->default(0);
            $table->float('exchange_rate')->default(1);
            $table->float('ac_debtor')->default(0);
            $table->float('ac_creditor')->default(0);
            $table->foreignId('account_id')->references('id')->on('accounts');
            $table->integer('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->integer('transaction_type')->default(1)->index(); // inputs _ outputs _  
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
        Schema::dropIfExists('entry_transactions');
    }
};
