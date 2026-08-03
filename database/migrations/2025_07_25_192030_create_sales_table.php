<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type_sale')->nullable()->comment("0 directa, 1 antic, 2 deliv directa, 3 deliv \r\nant");
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('voucher_type', 20)->nullable();
            $table->string('voucher_id', 30)->nullable();
            $table->string('voucher_file')->nullable();
            $table->string('number', 20)->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('headquarter_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('cliente', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->decimal('total', 10, 2);
            $table->date('fecha_entrega')->nullable();
            $table->string('direccion')->nullable();
            $table->text('referencia')->nullable();
            $table->text('observacion')->nullable();
            $table->dateTime('fecha');
            $table->string('foto')->nullable();
            $table->tinyInteger('status')->nullable()->comment("0 entregado, 1 no entregado");
            $table->boolean('estado')->default(0);
            $table->timestamps();
            $table->tinyInteger('turno')->nullable()->comment("0 mañana, 1 tarde");
            $table->unsignedBigInteger('sede_recojo')->nullable();
            $table->string('hora_entrega', 15)->nullable();
            
            $table->foreign('user_id', 'fk_sales_user_id')->references('id')->on('usuarios');
            $table->foreign('sede_recojo', 'fk_sede_recojo')->references('id')->on('headquarters');
            $table->foreign('client_id', 'sales_client_id_fk')->references('id')->on('clients')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('headquarter_id', 'sales_ibfk_1')->references('id')->on('headquarters')->onDelete('set NULL');
            $table->foreign('table_id', 'sales_table_id_foreign')->references('id')->on('tables')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
