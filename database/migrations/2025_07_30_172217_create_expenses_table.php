<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('tipo_comprobante');
            $table->string('invoice_number')->nullable();
            $table->date('date')->default('current_timestamp()');
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->tinyInteger('turno')->nullable();
            $table->boolean('estado')->default(0)->comment("0: activo, 1: eliminado");
            $table->timestamps();
            
            $table->foreign('payment_method_id', 'expenses_payment_method_id_foreign')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->foreign('supplier_id', 'fk_expenses_supplier')->references('id')->on('suppliers');
            $table->foreign('sede_id', 'fk_sede_id')->references('id')->on('headquarters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
