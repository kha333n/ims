<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price'); // stored in paisas
            $table->unsignedInteger('quantity')->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('color')->nullable();
            $table->string('category')->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
