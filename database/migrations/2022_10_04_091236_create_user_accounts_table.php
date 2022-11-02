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
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('account_id')->nullable()->references('id')->on('accounts');
            $table->foreignId('currency_id')->nullable()->references('id')->on('accounts');
            $table->string('name')->nullable(); // 0 inactive 1 active  
            $table->integer('status')->default(1); // 0 inactive 1 active  
            $table->boolean('main')->default(0); // 0 inactive 1 active  
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
        Schema::dropIfExists('user_accounts');
    }
};
