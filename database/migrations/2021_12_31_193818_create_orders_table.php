<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('status', 50)->default('pending');
            $table->text('status_message')->nullable();
            $table->bigInteger('payment_method_id')->unsigned();
            $table->bigInteger('shipping_address_id')->unsigned();
            $table->decimal('total_price');
            $table->string('paypal_order_identifier')->nullabe();
            $table->string('paypal_email')->nullabe();
            $table->string('paypal_given_name')->nullabe();
            $table->string('paypal_payer_id')->nullabe();
            $table->timestamps();


            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('shipping_address_id')->references('id')->on('shipping_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
