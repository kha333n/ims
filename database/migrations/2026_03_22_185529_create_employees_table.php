<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'sale_man' or 'recovery_man'
            $table->string('phone')->nullable();
            $table->string('cnic')->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('commission_percent')->default(0); // for sale men
            $table->unsignedInteger('salary')->nullable();             // for recovery men (paisas)
            $table->string('area')->nullable();                        // for recovery men
            $table->string('rank')->nullable();                        // for recovery men
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
