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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('id_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')->nullable()->references('id')->on('countries');
            $table->foreignId('city_id')->nullable()->references('id')->on('cities');
            $table->foreignId('default_currency_id')->nullable()->references('id')->on('currencies');
            $table->integer('type')->default(0);
            $table->foreignId('account_id')->nullable()->references('id')->on('accounts');
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
        Schema::dropIfExists('parties');
    }
};
