<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLegacyPosColumns extends Migration
{
    /**
     * Bring legacy product and storage tables up to the minimum schema used
     * by the sales screen without removing or rewriting existing records.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->default(0);
            });
        }

        if (!Schema::hasTable('storage3s')) {
            return;
        }

        if (!Schema::hasColumn('storage3s', 'headquarter_id')) {
            Schema::table('storage3s', function (Blueprint $table) {
                $table->unsignedBigInteger('headquarter_id')->nullable();
            });
        }

        if (!Schema::hasColumn('storage3s', 'product_id')) {
            Schema::table('storage3s', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable();
            });
        }

        if (!Schema::hasColumn('storage3s', 'quantity')) {
            Schema::table('storage3s', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('storage3s', 'estado')) {
            Schema::table('storage3s', function (Blueprint $table) {
                $table->boolean('estado')->default(0);
            });
        }
    }

    /**
     * Keep legacy data intact on rollback.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
