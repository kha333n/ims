<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_cost'); // paisas
            $table->date('purchase_date');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
