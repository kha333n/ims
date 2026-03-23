<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); // Acc#
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_man_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('recovery_man_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('slip_number')->nullable();
            $table->date('sale_date');
            $table->unsignedInteger('total_amount');      // paisas
            $table->unsignedInteger('advance_amount')->default(0); // paisas
            $table->unsignedInteger('discount_amount')->default(0); // paisas
            $table->unsignedInteger('remaining_amount');  // paisas
            $table->string('installment_type'); // Daily, Weekly, Monthly
            $table->unsignedTinyInteger('installment_day')->nullable(); // day of week/month
            $table->unsignedInteger('installment_amount'); // paisas per installment
            $table->string('status')->default('active'); // active, closed
            $table->date('closed_at')->nullable();
            $table->string('discount_slip')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
