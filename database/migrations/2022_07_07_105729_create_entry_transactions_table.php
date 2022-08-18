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
            $table->float('debtor')->default(0);
            $table->float('creditor')->default(0);
            $table->foreignId('account_id')->references('id')->on('accounts');
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
