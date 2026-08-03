<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaloteoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paloteo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('headquarter_id');
            $table->unsignedBigInteger('product_id');
            $table->double('stock_inicial')->default(0);
            $table->double('stock_final')->default(0);
            $table->double('venta_teorica')->default(0);
            $table->double('venta_real')->default(0);
            $table->string('turno', 50)->nullable();
            $table->date('fecha')->nullable();
            $table->tinyInteger('encuadre')->nullable()->comment("0: encuadra, 1: no encuandra");
            $table->timestamps();
            
            $table->foreign('headquarter_id', 'paloteo_ibfk_1')->references('id')->on('headquarters')->onDelete('cascade');
            $table->foreign('product_id', 'paloteo_ibfk_2')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paloteo');
    }
}
