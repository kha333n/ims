<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_change_log', function (Blueprint $table) {
            $table->id();
            $table->string('relative_path');
            $table->string('action'); // created, modified, deleted
            $table->integer('file_size')->nullable();
            $table->string('file_hash', 64)->nullable(); // SHA-256
            $table->boolean('backed_up')->default(false);
            $table->timestamps();

            $table->index(['backed_up', 'created_at']);
            $table->index('relative_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_change_log');
    }
};
