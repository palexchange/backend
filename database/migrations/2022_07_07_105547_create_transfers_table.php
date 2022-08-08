<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->dateTime('issued_at')->default(DB::raw('now()'));
            $table->integer('type')->default(0);
            $table->foreignId('sender_party_id')->nullable()->references('id')->on('parties');
            $table->foreignId('receiver_party_id')->nullable()->references('id')->on('parties');
            $table->integer('commision_side')->default(0);
            $table->float('commision')->default(0);
            $table->boolean('is_commision_percentage')->default(0);
            $table->float('received_amount');
            $table->float('to_send_amount');
            $table->foreignId('received_currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('delivery_currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('reference_currency_id')->nullable()->references('id')->on('currencies');
            $table->float('exchange_rate_to_reference_currency');
            $table->float('exchange_rate_to_delivery_currency');
            $table->float('other_amounts_on_receiver')->default(0);
            $table->float('other_amounts_on_sender')->default(0);
            $table->foreignId('office_id')->nullable()->references('id')->on('offices');
            $table->foreignId('office_currency_id')->nullable()->references('id')->on('currencies');
            $table->float('office_commision')->default(0);
            $table->integer('office_commision_type')->default(0);
            $table->float('returned_commision')->default(0);
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
        Schema::dropIfExists('transfers');
    }
};
