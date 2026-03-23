<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // sale, payment, return, closure, activation, discount, purchase
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('debit')->default(0);   // money coming in (paisas)
            $table->integer('credit')->default(0);   // money going out (paisas)
            $table->integer('balance_after')->nullable(); // account remaining after this event
            $table->string('description');
            $table->json('meta')->nullable(); // extra context (old_status, new_status, etc.)
            $table->timestamp('event_date');
            $table->timestamps();

            $table->index('event_type');
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledger');
    }
};
