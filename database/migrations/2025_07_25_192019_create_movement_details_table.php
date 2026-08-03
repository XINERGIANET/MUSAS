<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movement_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->default(0);
            $table->unsignedBigInteger('movement_id');
            $table->bigInteger('quantity')->default(0);
            $table->unsignedTinyInteger('transformado')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->boolean('estado')->default(0);
            
            $table->foreign('movement_id', 'fk_movement_id')->references('id')->on('movements')->onDelete('cascade');
            $table->foreign('product_id', 'fk_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movement_details');
    }
}
