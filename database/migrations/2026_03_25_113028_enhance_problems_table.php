<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->string('status')->default('open')->after('closed');
            $table->string('severity')->nullable()->after('status');
            $table->foreignId('recovery_man_id')->nullable()->after('severity')->constrained('employees')->nullOnDelete();
        });

        // Migrate existing closed=true to status='resolved'
        DB::table('problems')->where('closed', true)->update(['status' => 'resolved']);
    }

    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropForeign(['recovery_man_id']);
            $table->dropColumn(['status', 'severity', 'recovery_man_id']);
        });
    }
};
