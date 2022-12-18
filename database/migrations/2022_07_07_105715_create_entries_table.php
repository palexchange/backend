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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->bigInteger('number')->default(0);
            $table->string('statement')->nullable();
            $table->integer('status')->default(0);
            $table->integer('document_type')->nullable();
            $table->integer('document_sub_type')->default(3); // 1=>transfer , 2 => exchange ,3 => fund_adjusment , 4=>inputs , 5 =,outputs , 6=profit_and_lose_transaction
            $table->unsignedBigInteger('document_id')->nullable();
            $table->bigInteger('document_number')->nullable();
            $table->foreignId('ref_currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('inverse_entry_id')->nullable()->references('id')->on('entries');
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
        Schema::dropIfExists('entries');
    }
};
