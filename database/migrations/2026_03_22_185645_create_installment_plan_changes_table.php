<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plan_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('old_type');
            $table->unsignedTinyInteger('old_day')->nullable();
            $table->unsignedInteger('old_amount'); // paisas
            $table->string('new_type');
            $table->unsignedTinyInteger('new_day')->nullable();
            $table->unsignedInteger('new_amount'); // paisas
            $table->date('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plan_changes');
    }
};
