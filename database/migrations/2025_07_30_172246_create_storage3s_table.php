<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorage3sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage3s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('headquarter_id')->index('storage3s_headquarter_id_foreign');
            $table->unsignedBigInteger('product_id')->index('storage3s_product_id_foreign');
            $table->decimal('quantity', 10, 2);
            $table->unsignedDecimal('stock_minimo', 10, 2)->nullable();
            $table->boolean('estado')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage3s');
    }
}
