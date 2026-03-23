<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('manager')->nullable();
            $table->string('checker')->nullable();
            $table->string('branch')->nullable();
            $table->text('problem_text')->nullable();
            $table->date('previous_promise_date')->nullable();
            $table->date('new_commitment_date')->nullable();
            $table->text('action_taken')->nullable();
            $table->boolean('closed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
