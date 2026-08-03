<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashClosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_closes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->unsignedTinyInteger('turno')->default(0)->comment("0 mañana 1 tarde");
            $table->unsignedBigInteger('usuario_id')->default(0);
            $table->unsignedBigInteger('headquarter_id')->nullable()->comment("null si es delivery");
            $table->timestamps();
            $table->tinyInteger('estado');
            
            $table->foreign('headquarter_id', 'fk_cash_headquarter_id')->references('id')->on('headquarters')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('usuario_id', 'fk_cash_usuario_id')->references('id')->on('usuarios')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_closes');
    }
}
