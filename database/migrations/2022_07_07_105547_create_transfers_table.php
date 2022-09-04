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
            $table->integer('status')->default(0); // 
            $table->integer('number')->nullable(); // 
            $table->integer('delivering_type')->default(1); // 1=> تسليم يد /
            $table->foreignId('sender_party_id')->nullable()->references('id')->on('parties');
            $table->string('sender_id_no')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('sender_address')->nullable();
            $table->string('sender_notes')->nullable();
            $table->foreignId('receiver_party_id')->nullable()->references('id')->on('parties');
            $table->string('receiver_id_no')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_address')->nullable();
            $table->string('receiver_notes')->nullable();
            $table->foreignId('receiver_country_id')->nullable()->references('id')->on('countries');
            $table->foreignId('receiver_city_id')->nullable()->references('id')->on('cities');
            $table->integer('commission_side')->default(1); // 1 sender ,  2 receiver
            $table->float('commission')->default(0);
            $table->boolean('is_commission_percentage')->default(0);
            $table->float('received_amount');
            $table->float('to_send_amount');
            $table->foreignId('received_currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('delivery_currency_id')->nullable()->references('id')->on('currencies');
            $table->foreignId('reference_currency_id')->nullable()->references('id')->on('currencies');
            $table->float('exchange_rate_to_reference_currency');
            $table->float('exchange_rate_to_delivery_currency');
            $table->float('other_amounts_on_receiver')->default(0);
            $table->float('other_amounts_on_sender')->default(0);
            $table->foreignId('office_id')->nullable()->references('id')->on('parties');
            $table->foreignId('office_currency_id')->nullable()->references('id')->on('currencies');
            $table->float('office_commission')->default(0);
            $table->float('exchange_rate_to_office_currency')->default(0);
            $table->integer('office_commission_type')->default(0);
            $table->float('returned_commission')->default(0);
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
