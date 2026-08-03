<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('insumo_id')->nullable()->index('fk_purchase_details_insumos');
            $table->decimal('quantity', 10, 2)->default(0.00);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
            
            $table->foreign('product_id', 'purchase_details_product_fk')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('purchase_id', 'purchase_details_purchase_fk')->references('id')->on('purchases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_details');
    }
}
