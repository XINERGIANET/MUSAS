<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_price', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('headquarter_id')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->boolean('estado')->default(0);
            $table->timestamps();
            
            $table->foreign('headquarter_id', 'fk_headquarters')->references('id')->on('headquarters')->onDelete('set NULL');
            $table->foreign('product_id', 'fk_products')->references('id')->on('products')->onDelete('set NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_price');
    }
}
