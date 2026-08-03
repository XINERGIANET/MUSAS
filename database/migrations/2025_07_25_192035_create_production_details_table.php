<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 6, 2)->default(0.00);
            $table->timestamps()->default('current_timestamp()');
            
            $table->foreign('product_id', 'fk_production_details_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('production_id', 'fk_production_details_production_id')->references('id')->on('productions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_details');
    }
}
