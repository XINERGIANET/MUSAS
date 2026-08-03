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
            $table->unsignedBigInteger('staff_id')->nullable()->index('staff_id');
            $table->unsignedBigInteger('sede_id')->nullable()->index('sede_id');
            $table->tinyInteger('turno')->comment("0 mañana, 1 tarde");
            
            $table->foreign('rol_id', 'fk_rol_usuario')->references('id')->on('roles')->onDelete('restrict')->onUpdate('restrict');
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
