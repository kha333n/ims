<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('price', 'sale_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('purchase_price')->default(0)->after('sale_price'); // paisas
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('sale_price', 'price');
        });
    }
};
