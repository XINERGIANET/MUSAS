<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('headquarter_id');
            $table->unsignedBigInteger('headquarter_to_id')->nullable();
            $table->date('date');
            $table->boolean('estado')->default(0);
            $table->unsignedTinyInteger('tipo');
            $table->timestamps();
            $table->unsignedTinyInteger('turno')->nullable()->comment("0 mañana, 1 tarde");
            
            $table->foreign('tipo', 'movements_tipo_id')->references('id')->on('movement_types')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('headquarter_to_id', 'transfer_headquarter_to_id_foreign')->references('id')->on('headquarters')->onDelete('cascade');
            $table->foreign('headquarter_id', 'transfers_headquarter_id_foreign')->references('id')->on('headquarters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movements');
    }
}
