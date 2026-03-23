<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // CID
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('cnic')->nullable();
            $table->string('reference')->nullable();
            $table->string('home_address')->nullable();
            $table->string('shop_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
