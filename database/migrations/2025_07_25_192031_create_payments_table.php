<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedDecimal('monto', 10, 2)->default(0.00);
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedTinyInteger('estado')->default(0);
            $table->date('fecha')->nullable();
            $table->unsignedTinyInteger('turno')->nullable()->comment("0 mañana, 1 tarde");
            $table->timestamps();
            
            $table->foreign('payment_method_id', 'fk_payment_method_payment')->references('id')->on('payment_methods')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('sale_id', 'fk_sale_payment')->references('id')->on('sales')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
