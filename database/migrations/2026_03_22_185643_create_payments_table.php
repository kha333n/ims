<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount'); // paisas
            $table->string('transaction_type')->default('installment'); // installment, advance, manual
            $table->date('payment_date');
            $table->foreignId('collected_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
