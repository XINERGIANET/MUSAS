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
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('headquarter_id');
            $table->integer('quantity')->default(0);
            $table->timestamps()->default('current_timestamp()');
            
            $table->foreign('headquarter_id', 'fk_stock_inicial_headquarter')->references('id')->on('headquarters')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id', 'fk_stock_inicial_product')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
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
