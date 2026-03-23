<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Model name: ProductReturn (to avoid PHP reserved word conflict)
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedInteger('returning_amount'); // paisas — amount to deduct from balance
            $table->date('return_date');
            $table->string('reason')->nullable();
            $table->string('inventory_action')->default('restock'); // restock, scrap
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
