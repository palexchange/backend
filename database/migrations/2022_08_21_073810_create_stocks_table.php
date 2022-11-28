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
    // من شيكل الي دينار
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('ref_currency_id')->nullable()->references('id')->on('currencies');
            $table->float('start_selling_price', 12, 5)->default(0);
            $table->float('final_selling_price', 12, 5)->default(0);
            $table->float('start_purchasing_price', 12, 5)->default(0);
            $table->float('final_purchasing_price', 12, 5)->default(0);
            $table->boolean('preferd')->default(false);
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
        Schema::dropIfExists('stocks');
    }
};
