<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 100)->unique('usuarios_email_unique');
            $table->string('password');
            $table->integer('pin')->nullable();
            $table->unsignedBigInteger('rol_id');
            $table->boolean('activo')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->tinyInteger('turno')->comment("0 mañana, 1 tarde");
            
            $table->foreign('staff_id', 'FK_usuarios_staff')->references('id')->on('staff');
            $table->foreign('sede_id', 'fk_usuarios_sede_id')->references('id')->on('headquarters');
            $table->foreign('rol_id', 'usuarios_rol_id_foreign')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
