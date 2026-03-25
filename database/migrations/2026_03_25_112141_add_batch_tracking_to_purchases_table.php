<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('id');
            $table->integer('remaining_qty')->default(0)->after('quantity');
        });

        // Backfill: set remaining_qty = quantity for existing purchases
        // and generate batch numbers for existing rows
        DB::table('purchases')->orderBy('id')->each(function ($purchase) {
            DB::table('purchases')->where('id', $purchase->id)->update([
                'remaining_qty' => $purchase->quantity,
                'batch_number' => 'B-'.str_pad($purchase->id, 4, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'remaining_qty']);
        });
    }
};
