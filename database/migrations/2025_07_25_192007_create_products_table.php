<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('category_id')->default(0);
            $table->unsignedBigInteger('presentation_id')->nullable();
            $table->string('unidad_medida');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->boolean('estado')->default(0);
            $table->timestamps();
            $table->string('observacion')->nullable()->comment("Tipo de Producto");
            $table->integer('product_categorie_id')->nullable();
            
            $table->foreign('category_id', 'fk_category_id')->references('id')->on('category')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('presentation_id', 'fk_presentacion_id')->references('id')->on('presentation')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('product_categorie_id', 'fk_products_product_categorie')->references('id')->on('product_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
