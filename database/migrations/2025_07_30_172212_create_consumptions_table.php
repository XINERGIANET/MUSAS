<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->text('notes')->nullable();
            $table->tinyInteger('merma')->nullable();
            $table->string('area', 20)->default('0');
            $table->boolean('estado')->default(0);
            $table->unsignedBigInteger('staff_id');
            $table->date('date')->default('curdate()');
            $table->timestamps();
            
            $table->foreign('product_id', 'fk_product_id_products')->references('id')->on('products')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('staff_id', 'fk_staf__id_staff')->references('id')->on('staff')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumptions');
    }
}
