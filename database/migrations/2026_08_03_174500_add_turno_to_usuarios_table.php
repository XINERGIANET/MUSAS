<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTurnoToUsuariosTable extends Migration
{
    /**
     * Add the shift column required by the authentication layout to legacy
     * usuarios tables.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('usuarios') && !Schema::hasColumn('usuarios', 'turno')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->tinyInteger('turno')->default(0);
            });
        }
    }

    /**
     * Preserve existing user records on rollback.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
