<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockInicialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_inicial', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->unsignedBigInteger('product_id')->index('fk_stock_inicial_product');
            $table->unsignedBigInteger('headquarter_id')->index('fk_stock_inicial_headquarter');
            $table->integer('quantity')->default(0);
            $table->timestamps()->default('current_timestamp()');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_inicial');
    }
}
