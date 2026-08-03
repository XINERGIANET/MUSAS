<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('nombre', 100);
            $table->integer('estado')->default(0)->comment("0: Activo, 1: Inactivo");
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
            
            $table->foreign('category_id', 'fk_product_categories_category')->references('id')->on('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
