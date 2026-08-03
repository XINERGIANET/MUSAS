<?php

use IlluminateDatabaseMigrationsMigration;
use IlluminateDatabaseSchemaBlueprint;
use IlluminateSupportFacadesSchema;

class AddMissingStockMinimoColumns extends Migration
{
    /**
     * Add the stock threshold used by the notification queries to legacy
     * storage tables that predate this column.
     *
     * @return void
     */
    public function up()
    {
        foreach (['storage2s', 'storage3s'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'stock_minimo')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unsignedDecimal('stock_minimo', 10, 2)->nullable();
                });
            }
        }
    }

    /**
     * This reconciliation migration intentionally preserves legacy data.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
