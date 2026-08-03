<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingreso_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('ingreso_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 6, 2)->default(0.00);
            $table->timestamps()->default('current_timestamp()');
            
            $table->foreign('ingreso_id', 'fk_ingreso_details_ingreso_id')->references('id')->on('ingresos')->onDelete('cascade');
            $table->foreign('product_id', 'fk_ingreso_details_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingreso_details');
    }
}
