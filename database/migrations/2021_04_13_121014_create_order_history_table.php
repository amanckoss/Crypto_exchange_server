<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('stock_id')->unsigned();
            $table->integer("amount");
            $table->decimal("purchase_price")->nullable();
            $table->bigInteger('sell_trader_id')->unsigned();
            $table->bigInteger('buy_trader_id')->unsigned();
            $table->timestamps();

            $table->foreign("stock_id")->references('id')->on('stock');
            $table->foreign("sell_trader_id")->references('id')->on('users');
            $table->foreign("buy_trader_id")->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_history');
    }
}
