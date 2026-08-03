<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompleteLegacyProductsColumns extends Migration
{
    /**
     * Add the product attributes expected by the POS to databases created
     * before the current products schema.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'product_categorie_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('product_categorie_id')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'presentation_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('presentation_id')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'unidad_medida')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('unidad_medida')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'unit_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('unit_price', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'observacion')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('observacion')->nullable();
            });
        }
    }

    /**
     * Keep legacy product records intact on rollback.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
