<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 20)->nullable()->unique('staff_dni_unique');
            $table->string('nombre')->nullable();
            $table->string('telefono', 15)->nullable();
            $table->bigInteger('puesto_id')->nullable()->index('staff_puesto_id_foreign');
            $table->unsignedBigInteger('headquarter_id')->nullable()->index('staff_headquarter_id_foreign');
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('sueldo', 8, 2)->nullable();
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
        Schema::dropIfExists('staff');
    }
}
